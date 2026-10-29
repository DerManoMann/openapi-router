<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests;

use Radebatz\OpenApi\Routing\RouteRegistration;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;

/**
 * Collects registrations instead of touching a framework router.
 */
final class RecordingAdapter implements RoutingAdapterInterface
{
    /** @var list<RouteRegistration> */
    public array $routes = [];

    public function __construct(
        private readonly bool $hasCachedRoutes = false,
    ) {
    }

    public function register(RouteRegistration $route): void
    {
        $this->routes[] = $route;
    }

    public function registerCached(): bool
    {
        return $this->hasCachedRoutes;
    }

    public function route(string $name): ?RouteRegistration
    {
        foreach ($this->routes as $route) {
            if ($route->custom[RoutingAdapterInterface::X_NAME] === $name) {
                return $route;
            }
        }

        return null;
    }
}
