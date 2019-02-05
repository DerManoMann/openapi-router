<?php

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
     */
    public function register(Operation $operation, array $parameters);
}
