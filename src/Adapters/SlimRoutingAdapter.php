<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Adapters;

use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;
use Slim\App;

/**
 * Slim routing adapter.
 */
class SlimRoutingAdapter implements RoutingAdapterInterface
{
    /** @var App */
    protected $app;

    /** @var array */
    protected $options = [];

    public function __construct(App $app, array $options = [])
    {
        $this->app = $app;
        $this->options = array_merge([
            static::OPTION_AUTO_REGEX => true,
        ], $options);
    }

    /**
     * @inheritdoc
     */
    public function register(Operation $operation, string $controller, array $parameters, array $custom): void
    {
        $path = $operation->path;

        $controller = str_replace('::', ':', $controller);

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
                    if ($this->options[static::OPTION_AUTO_REGEX]) {
                        $path = str_replace("{{$name}}", "{{$name}:[0-9]+}", $path);
                    }
                    break;
            }
        }

        $route = $this->app->map([strtoupper($operation->method)], $path, $controller);
        if ($custom[static::X_NAME]) {
            $route->setName($custom[static::X_NAME]);
        }

        foreach ($custom[static::X_MIDDLEWARE] as $middleware) {
            $route->add($middleware);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerCached(): bool
    {
        return false;
    }
}
