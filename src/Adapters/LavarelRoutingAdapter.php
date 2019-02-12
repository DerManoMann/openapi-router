<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Adapters;

use Illuminate\Support\Facades\Route;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;

/**
 * Lavarel routing adapter.
 */
class LavarelRoutingAdapter implements RoutingAdapterInterface
{
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

        Route::match($operation->method, $path, $operationId);
    }
}
