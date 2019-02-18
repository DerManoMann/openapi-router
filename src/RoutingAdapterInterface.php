<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing;

use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;

/**
 * Routing adapter interface.
 */
interface RoutingAdapterInterface
{
    /**
     * Register a route.
     *
     * @param Operation   $operation  The route URI pattern
     * @param Parameter[] $parameters URI path parameters
     * @param array       $custom     Custom properties `x={}`
     */
    public function register(Operation $operation, array $parameters, array $custom);
}
