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
    /** @var Application $app */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Operation $operation, array $parameters, array $custom)
    {
        $path = $operation->path;
        $operationId = str_replace('::__invoke', '', $operation->operationId);
        $operationId = str_replace('App\\Http\\Controllers\\', '', $operationId);

        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {
            $name = $parameter->name;
            // TODO
        }

        /** @var Router $router */
        $router = $this->app->router;

        $action = [
            'uses' => $operationId
        ];
        if ($custom['name']) {
            $action['as'] = $custom['name'];
        }

        $router->addRoute(strtoupper($operation->method), $path, $action);
    }
}
