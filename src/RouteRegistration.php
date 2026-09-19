<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing;

/**
 * Everything a routing adapter needs to register one route.
 *
 * Deliberately plain data, decoupled from swagger-php's `Spec\*` DTOs: every root DTO in the
 * spec pipeline carries a live `\Reflector` internally (`AbstractAttribute::getReflector()`)
 * with no `__serialize()`/`__sleep()` to strip it, so a `Spec\Operation` cannot survive a
 * PSR-16 cache round-trip. `OpenApiRouter` resolves everything a route needs while it still
 * has the `Specification` in hand and hands adapters this instead — cacheable, and it means
 * an adapter never needs to depend on swagger-php at all.
 */
final class RouteRegistration
{
    /**
     * @param array<string,array{required: bool, type: ?string, pattern: ?string}> $parameters URI parameter metadata, keyed by name, in reverse declaration order
     * @param array<string,mixed>                                                  $custom     `RoutingAdapterInterface::X_*` keys
     */
    public function __construct(
        public readonly string $path,
        public readonly string $method,
        public readonly string $controller,
        public readonly array $parameters,
        public readonly array $custom,
    ) {
    }
}
