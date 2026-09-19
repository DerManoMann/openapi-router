<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Laravel;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\SimpleCache\CacheInterface;
use Radebatz\OpenApi\Routing\Adapters\LaravelRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class CachingTest extends LaravelTestCase
{
    use CallsApplicationTrait;

    public static function reloadTests(): \Iterator
    {
        yield 'no-cache-reload' => [null, true, false];
        yield 'no-cache-no-reload' => [null, false, false];
        yield 'cache-reload' => [new Psr16Cache(new ArrayAdapter()), false, true];
        yield 'cache-no-reload' => [new Psr16Cache(new ArrayAdapter()), true, false];
    }

    #[DataProvider('reloadTests')]
    public function testReload(?CacheInterface $cache, bool $reload, bool $openapisCached): void
    {
        (new OpenApiRouter($this->getFixtureFinder(), new LaravelRoutingAdapter($app = $this->getApp())))
            ->withReload($reload)
            ->withCache($cache)
            ->registerRoutes();

        /** @var Router $router */
        $router = $app['router'];
        $this->assertInstanceOf(Route::class, $router->getRoutes()->getByName('getya'));

        $this->assertSame($openapisCached, $cache instanceof CacheInterface && $cache->has(OpenApiRouter::CACHE_KEY_ROUTES));
    }

    protected function getApp(): Application
    {
        $app = new Application();
        Facade::setFacadeApplication($app);

        return $app;
    }
}
