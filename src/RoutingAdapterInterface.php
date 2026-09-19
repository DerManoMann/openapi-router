<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing;

/**
 * Routing adapter interface.
 */
interface RoutingAdapterInterface
{
    public const X_NAME = 'name';

    public const X_MIDDLEWARE = 'middleware';

    /**
     * Register a route.
     */
    public function register(RouteRegistration $route): void;

    /**
     * Register routes cached on framework level (optional).
     *
     * @return bool `true` if cached routes loaded
     */
    public function registerCached(): bool;
}
