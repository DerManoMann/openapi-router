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
     * @param array<string,array{required: bool, type: ?string, pattern: ?string}> $parameters URI parameter metadata, keyed by name, in reverse declaration order
     * @param array<string,mixed>                                                  $custom     `RoutingAdapterInterface::X_*` keys
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
