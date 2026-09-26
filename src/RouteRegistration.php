<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing;

/**
 * Everything a routing adapter needs to register one route.
 *
 * Plain data rather than a `Spec\Operation`, because spec DTOs hold a live `\Reflector` with
 * no `__serialize()` and so cannot survive a PSR-16 cache round-trip. Adapters therefore need
 * no swagger-php dependency of their own.
 */
final readonly class RouteRegistration
{
    /**
     * @param string                                                               $path       URL path with OpenAPI placeholders, prefixes already composed — `/api/v1/pets/{id}`
     * @param string                                                               $method     HTTP method, upper case
     * @param string                                                               $controller `Fully\Qualified\Class::method`; `__invoke` for a single-action controller
     * @param array<string,array{required: bool, type: ?string, pattern: ?string}> $parameters path parameter metadata keyed by name, merged from the governing `PathItem` chain and the operation, in **reverse** path order — consecutive optional parameters nest (`/multi[/{foo}[/{bar}]]`), which requires bracketing the last one first. `type` is `string`, `integer`, `regex` or `null`; `pattern` accompanies `regex`
     * @param array<string,mixed>                                                  $custom     keyed by the `RoutingAdapterInterface::X_*` constants — `X_NAME` (`?string`) and `X_MIDDLEWARE` (`list<string>`) are always present
     */
    public function __construct(
        public string $path,
        public string $method,
        public string $controller,
        public array $parameters,
        public array $custom,
    ) {
    }
}
