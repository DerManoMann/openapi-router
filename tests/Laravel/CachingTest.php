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

    /**
     * Named arguments: two adjacent bools are too easy to transpose positionally, and a
     * transposed pair still passes — it just asserts the opposite of what the name says.
     */
    public static function reloadTests(): \Iterator
    {
        yield 'no-cache-reload' => ['cache' => null, 'reload' => true, 'routesCached' => false];
        yield 'no-cache-no-reload' => ['cache' => null, 'reload' => false, 'routesCached' => false];
        yield 'cache-reload' => ['cache' => new Psr16Cache(new ArrayAdapter()), 'reload' => true, 'routesCached' => false];
        yield 'cache-no-reload' => ['cache' => new Psr16Cache(new ArrayAdapter()), 'reload' => false, 'routesCached' => true];
    }

    #[DataProvider('reloadTests')]
    public function testReload(?CacheInterface $cache, bool $reload, bool $routesCached): void
    {
        (new OpenApiRouter($this->getFixtureFinder(), new LaravelRoutingAdapter($app = $this->getApp())))
            ->setReload($reload)
            ->setCache($cache)
            ->registerRoutes();

        /** @var Router $router */
        $router = $app['router'];
        $this->assertInstanceOf(Route::class, $router->getRoutes()->getByName('getya'));

        $this->assertSame($routesCached, $cache instanceof CacheInterface && $cache->has(OpenApiRouter::CACHE_KEY_ROUTES));
    }

    protected function getApp(): Application
    {
        $app = new Application();
        Facade::setFacadeApplication($app);

        return $app;
    }
}
