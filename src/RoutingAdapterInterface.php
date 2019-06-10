<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing;

use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;

/**
 * Routing adapter interface.
 */
interface RoutingAdapterInterface
{
    public const X_NAME = 'name';
    public const X_MIDDLEWARE = 'middleware';

    public const OPTIONS_NAMESPACE = 'namespace';

    /**
     * Register a route.
     *
     * @param Operation $operation  The route URI pattern
     * @param array     $parameters URI parameter meta data in reverse order
     * @param array     $custom     Custom properties (e.g.`x={}`)
     */
    public function register(Operation $operation, string $controller, array $parameters, array $custom): void;

    /**
     * Register routes cached on framework level (optional).
     *
     * @return bool `true` if cached routes loaded
     */
    public function registerCached(): bool;
}
