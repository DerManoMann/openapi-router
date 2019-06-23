<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Adapters;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;

/**
 * Laravel routing adapter.
 */
class LaravelRoutingAdapter implements RoutingAdapterInterface
{
    /** @var Application $app */
    protected $app;

    /** @var array */
    protected $options = [];

    public function __construct(Application $app, array $options = [])
    {
        $this->app = $app;
        $this->options = $options + [self::OPTIONS_NAMESPACE => 'App\\Http\\Controllers\\'];
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

        $where = [];
        /** @var Parameter $parameter */
        foreach ($parameters as $name => $parameter) {
            switch ($parameter['type']) {
                case 'regex':
                    if ($pattern = $parameter['pattern']) {
                        $where[$name] = $pattern;
                    }
                    break;

                case 'integer':
                    $where[$name] = '[0-9]+';
                    break;
            }
        }

        /** @var Router $router */
        $router = $this->app->get('router');

        $action = [
            'uses' => str_replace('::', '@', $controller),
        ];
        if ($custom[static::X_NAME]) {
            $action['as'] = $custom[static::X_NAME];
        }

        $router
            ->addRoute(strtoupper($operation->method), $path, $action)
            ->middleware($custom[static::X_MIDDLEWARE])
            ->where($where);
    }

    /**
     * {@inheritdoc}
     */
    public function registerCached(): bool
    {
        return false;
    }
}
