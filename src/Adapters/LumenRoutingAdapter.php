<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Adapters;

use Laravel\Lumen\Application;
use Laravel\Lumen\Routing\Router;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;

/**
 * Lumen routing adapter.
 */
class LumenRoutingAdapter implements RoutingAdapterInterface
{
    /** @var Application */
    protected $app;

    /** @var array */
    protected $options = [];

    public function __construct(Application $app, array $options = [])
    {
        $this->app = $app;
        $this->options = $options + [
                self::OPTIONS_AUTO_REGEX => true,
                self::OPTIONS_NAMESPACE => 'App\\Http\\Controllers\\',
            ];
    }

    /**
     * {@inheritdoc}
     */
    public function register(Operation $operation, string $controller, array $parameters, array $custom): void
    {
        $path = $operation->path;
        $controller = str_replace('::__invoke', '', $controller);
        if ($namespace = $this->options[self::OPTIONS_NAMESPACE]) {
            $controller = str_replace($namespace, '', $controller);
        }

        /** @var Parameter $parameter */
        foreach ($parameters as $name => $parameter) {
            if (!$parameter['required']) {
                if (false !== strpos($path, $needle = "/{{$name}}[/{")) {
                    // multiple optional parameters
                    $path = preg_replace("#/{{$name}}(\[?.*}\])#", "[/{{$name}}$1]", $path);
                } else {
                    $path = str_replace("/{{$name}}", "[/{{$name}}]", $path);
                }
            }

            switch ($parameter['type']) {
                case 'regex':
                    if ($pattern = $parameter['pattern']) {
                        $path = str_replace("{{$name}}", "{{$name}:$pattern}", $path);
                    }
                    break;

                case 'integer':
                    if ($this->options[self::OPTIONS_AUTO_REGEX]) {
                        $path = str_replace("{{$name}}", "{{$name}:[0-9]+}", $path);
                    }
                    break;
            }
        }

        /** @var Router $router */
        $router = $this->app->router;

        $action = [
            'uses' => str_replace('::', '@', $controller),
        ];
        if ($custom[static::X_NAME]) {
            $action['as'] = $custom[static::X_NAME];
        }

        $router->addRoute(strtoupper($operation->method), $path, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function registerCached(): bool
    {
        return false;
    }
}
