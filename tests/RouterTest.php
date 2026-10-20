<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests;

use OpenApi\Builder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Radebatz\OpenApi\Routing\RouteRegistration;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;
use Radebatz\OpenApi\Routing\Tests\Concerns\Fixtures;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers\InheritedController;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers\NamedRouteController;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers\SharedParameterController;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\BarMiddleware;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\FooMiddleware;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Routing\FacetsController;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Routing\Lang;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Routing\OperationIdController;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Routing\RefParameterController;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Routing\TenantParameter;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Routing\UnionTypeController;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * Framework-agnostic cover for the router itself — the configuration API and the two places
 * routing metadata has to be resolved against the class hierarchy rather than the operation.
 */
final class RouterTest extends TestCase
{
    use Fixtures;

    #[Test]
    public function inheritsMiddlewareFromAnAncestorPathItem(): void
    {
        $route = $this->register(InheritedController::class)->route('inherited');

        $this->assertInstanceOf(RouteRegistration::class, $route);
        $this->assertSame('/base/inherited', $route->path);
        $this->assertSame(
            [FooMiddleware::class, BarMiddleware::class],
            $route->custom[RoutingAdapterInterface::X_MIDDLEWARE],
            "ancestor PathItem middleware applies first, then the operation's own",
        );
    }

    #[Test]
    public function readsPathParametersDeclaredOnThePathItem(): void
    {
        $route = $this->register(SharedParameterController::class)->route('shared');

        $this->assertInstanceOf(RouteRegistration::class, $route);
        $this->assertSame('/shared/{tenant}/items', $route->path);
        $this->assertSame(
            ['tenant' => ['required' => true, 'type' => 'integer', 'pattern' => null, 'default' => null]],
            $route->parameters,
        );
    }

    #[Test]
    public function readsAPathParameterThePathItemDeclaresByReference(): void
    {
        $route = $this->register(TenantParameter::class, RefParameterController::class)->route('ref');

        $this->assertInstanceOf(RouteRegistration::class, $route);
        $this->assertSame('/ref/{tenant}/items', $route->path);
        $this->assertSame(
            ['tenant' => ['required' => true, 'type' => 'integer', 'pattern' => null, 'default' => null]],
            $route->parameters,
            'the schema and `required` come from the component the reference points to',
        );
    }

    #[Test]
    public function aNullableTypeStillConstrainsTheParameter(): void
    {
        $route = $this->register(UnionTypeController::class)->route('nullable');

        $this->assertInstanceOf(RouteRegistration::class, $route);
        $this->assertSame(
            ['id' => ['required' => true, 'type' => 'integer', 'pattern' => null, 'default' => null]],
            $route->parameters,
            'OpenAPI 3.1 spells nullability as a type list; `null` is not routable, so it drops out',
        );
    }

    #[Test]
    public function aGenuineUnionConstrainsNothing(): void
    {
        $route = $this->register(UnionTypeController::class)->route('union');

        $this->assertInstanceOf(RouteRegistration::class, $route);
        $this->assertSame(
            ['id' => ['required' => true, 'type' => null, 'pattern' => null, 'default' => null]],
            $route->parameters,
            'two routable types leave no single constraint to apply',
        );
    }

    /**
     * @return iterable<string,array{string,array{required: bool, type: ?string, pattern: ?string, default: int|float|string|null}}>
     */
    public static function facets(): iterable
    {
        $uuid = '[\\da-fA-F]{8}-[\\da-fA-F]{4}-[\\da-fA-F]{4}-[\\da-fA-F]{4}-[\\da-fA-F]{12}';

        yield 'enum' => ['enum', ['required' => true, 'type' => 'regex', 'pattern' => 'en|de', 'default' => null]];
        yield 'enum by reference' => ['backed', ['required' => true, 'type' => 'regex', 'pattern' => 'en|de', 'default' => null]];
        yield 'integer enum' => ['number', ['required' => true, 'type' => 'regex', 'pattern' => '1|2', 'default' => null]];
        yield 'pattern wins over enum' => ['pattern', ['required' => true, 'type' => 'regex', 'pattern' => '[a-z]{2}', 'default' => null]];
        yield 'uuid' => ['uuid', ['required' => true, 'type' => 'regex', 'pattern' => $uuid, 'default' => null]];
        yield 'ulid' => ['ulid', ['required' => true, 'type' => 'regex', 'pattern' => '[0-7][0-9a-hjkmnp-tv-zA-HJKMNP-TV-Z]{25}', 'default' => null]];
        yield 'default' => ['page', ['required' => false, 'type' => 'integer', 'pattern' => null, 'default' => 1]];
    }

    /**
     * @param array{required: bool, type: ?string, pattern: ?string, default: int|float|string|null} $expected
     */
    #[Test]
    #[DataProvider('facets')]
    public function schemaFacetsBecomeRouteConstraints(string $name, array $expected): void
    {
        $route = $this->register(Lang::class, FacetsController::class)->route($name);

        $this->assertInstanceOf(RouteRegistration::class, $route);
        $this->assertSame([array_key_first($route->parameters) => $expected], $route->parameters);
    }

    #[Test]
    public function operationIdBecomesTheRouteNameByDefault(): void
    {
        $route = $this->register(OperationIdController::class)->routes[0] ?? null;

        $this->assertInstanceOf(RouteRegistration::class, $route);
        $this->assertSame('listWidgets', $route->custom[RoutingAdapterInterface::X_NAME]);
    }

    #[Test]
    public function operationIdAsNameCanBeTurnedOff(): void
    {
        $adapter = new RecordingAdapter();

        (new OpenApiRouter($this->reflect(OperationIdController::class), $adapter))
            ->setOperationIdAsName(false)
            ->registerRoutes();

        $route = $adapter->routes[0] ?? null;

        $this->assertInstanceOf(RouteRegistration::class, $route);
        $this->assertNull($route->custom[RoutingAdapterInterface::X_NAME]);
    }

    #[Test]
    public function anExplicitNameWinsOverTheOperationId(): void
    {
        $adapter = new RecordingAdapter();

        (new OpenApiRouter($this->reflect(InheritedController::class), $adapter))
            ->setOperationIdAsName(false)
            ->registerRoutes();

        $this->assertInstanceOf(RouteRegistration::class, $adapter->route('inherited'), 'x-name names a route even with the operationId off');
    }

    #[Test]
    public function withBuilderCanReplaceTheBuilder(): void
    {
        $adapter = new RecordingAdapter();
        $received = null;

        (new OpenApiRouter($this->reflect(NamedRouteController::class), $adapter))
            ->withBuilder(function (Builder $builder) use (&$received): Builder {
                $received = $builder;

                return $builder;
            })
            ->registerRoutes();

        $this->assertInstanceOf(Builder::class, $received, 'the hook receives the prepared builder');
        $this->assertNotEmpty($adapter->routes, 'a returned builder replaces the prepared one');
    }

    #[Test]
    public function loggerIsHandedToTheBuilder(): void
    {
        $logger = new class () extends AbstractLogger {
            /** @var list<string> */
            public array $records = [];

            public function log($level, \Stringable|string $message, array $context = []): void
            {
                $this->records[] = (string) $message;
            }
        };

        $seen = null;

        (new OpenApiRouter($this->reflect(NamedRouteController::class), new RecordingAdapter()))
            ->setLogger($logger)
            ->withBuilder(function (Builder $builder) use (&$seen): void {
                // `Builder::getLogger()` is protected; the point here is that the router set
                // it before handing the builder over, so read it where the hook would care
                $seen = (new \ReflectionProperty(Builder::class, 'logger'))->getValue($builder);
            })
            ->registerRoutes();

        $this->assertInstanceOf(LoggerInterface::class, $seen);
        $this->assertSame($logger, $seen, 'setLogger() applies before the withBuilder() hook runs');
    }

    #[Test]
    public function cachedRoutesSurviveAPsr16RoundTrip(): void
    {
        // serialised, so the cached value really is the plain data that came out of the scan
        $cache = new Psr16Cache(new ArrayAdapter(storeSerialized: true));

        $first = new RecordingAdapter();
        (new OpenApiRouter($this->getFixtureFinder(), $first))
            ->setReload(false)
            ->setCache($cache)
            ->registerRoutes();

        $this->assertNotEmpty($first->routes);
        $this->assertTrue($cache->has(OpenApiRouter::CACHE_KEY_ROUTES));

        $second = new RecordingAdapter();
        $routes = (new OpenApiRouter($this->getFixtureFinder(), $second))
            ->setReload(false)
            ->setCache($cache)
            ->registerRoutes();

        $this->assertNotNull($routes);
        $this->assertContainsOnlyInstancesOf(RouteRegistration::class, $second->routes);
        $this->assertEquals($first->routes, $second->routes, 'a cache hit registers exactly what the scan did');
    }

    #[Test]
    public function anAdapterWithItsOwnRouteCacheSkipsTheScan(): void
    {
        $adapter = new RecordingAdapter(hasCachedRoutes: true);

        $routes = (new OpenApiRouter($this->getFixtureFinder(), $adapter))
            ->setReload(false)
            ->registerRoutes();

        $this->assertNull($routes);
        $this->assertSame([], $adapter->routes);
    }

    #[Test]
    public function reloadingIgnoresTheAdaptersOwnRouteCache(): void
    {
        $adapter = new RecordingAdapter(hasCachedRoutes: true);

        $routes = (new OpenApiRouter($this->getFixtureFinder(), $adapter))->registerRoutes();

        $this->assertNotNull($routes);
        $this->assertNotEmpty($adapter->routes);
    }

    /**
     * @param class-string ...$classes
     */
    private function register(string ...$classes): RecordingAdapter
    {
        $adapter = new RecordingAdapter();
        (new OpenApiRouter(array_merge(...array_map($this->reflect(...), $classes)), $adapter))->registerRoutes();

        return $adapter;
    }

    /**
     * The class plus its ancestors — `addSource()` collects only what it is handed, so a
     * `PathItem` on a base class has to be in the sources to be seen at all.
     *
     * @param class-string $class
     *
     * @return list<\ReflectionClass<object>>
     */
    private function reflect(string $class): array
    {
        $reflectors = [];
        for ($current = new \ReflectionClass($class); $current !== false; $current = $current->getParentClass()) {
            $reflectors[] = $current;
        }

        return array_reverse($reflectors);
    }
}
