<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing;

/**
 * Translates extracted routes into a framework's own router.
 *
 * One implementation per framework. Adapters receive plain data and do not depend on
 * swagger-php.
 */
interface RoutingAdapterInterface
{
    /**
     * Key in `RouteRegistration::$custom` holding the route name, or `null` for an unnamed route.
     *
     * Also the `x-name` vendor property, which overrides the name the router would derive.
     */
    public const X_NAME = 'name';

    /**
     * Key in `RouteRegistration::$custom` holding the middleware list.
     *
     * Also the `x-middleware` vendor property, which appends to any `#[Middleware]` attributes.
     */
    public const X_MIDDLEWARE = 'middleware';

    /**
     * Register one route with the framework.
     *
     * Called once per operation, in scan order. Implementations translate
     * `RouteRegistration::$path` into the framework's own placeholder syntax — the parameter
     * metadata says which placeholders are optional and which carry a type or pattern.
     *
     * @param RouteRegistration $route the route to register
     */
    public function register(RouteRegistration $route): void;

    /**
     * Load routes the framework itself has cached, skipping the scan entirely.
     *
     * Only consulted when reloading is off. Returning `true` means the routes are registered
     * and {@see register()} will not be called; return `false` when the framework has no
     * route cache or it is empty.
     *
     * @return bool `true` if cached routes were loaded
     */
    public function registerCached(): bool;
}
