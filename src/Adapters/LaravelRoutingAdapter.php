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
    public function register(Operation $operation, array $parameters, array $custom): void
    {
        $path = $operation->path;
        $operationId = str_replace('::__invoke', '', $operation->operationId);
        if ($namespace = $this->options[self::OPTIONS_NAMESPACE]) {
            $operationId = str_replace($namespace, '', $operationId);
        }

        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {
            $name = $parameter->name;
            // TODO
        }

        /** @var Router $router */
        $router = $this->app->get('router');

        $action = [
            'uses' => str_replace('::', '@', $operationId),
        ];
        if ($custom[static::X_NAME]) {
            $action['as'] = $custom[static::X_NAME];
        }

        $route = $router->addRoute(strtoupper($operation->method), $path, $action);
        $route->middleware($custom[static::X_MIDDLEWARE]);
    }

    /**
     * {@inheritdoc}
     */
    public function registerCached(): bool
    {
        return false;
    }
}
