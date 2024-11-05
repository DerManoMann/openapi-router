<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Laravel;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use Psr\SimpleCache\CacheInterface;
use Radebatz\OpenApi\Routing\Adapters\LaravelRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class CachingTest extends LaravelTestCase
{
    use CallsApplicationTrait;

    public static function reloadTests(): iterable
    {
        return [
            [null, true, false],
            [null, false, false],
            [new Psr16Cache(new ArrayAdapter()), false, true],
            [new Psr16Cache(new ArrayAdapter()), true, false],
        ];
    }

    /**
     * @dataProvider reloadTests
     */
    public function testReload(?CacheInterface $cache, $reload, $openapisCached)
    {
        $options = [
            OpenApiRouter::OPTION_RELOAD => $reload,
            OpenApiRouter::OPTION_CACHE => $cache,
        ];

        (new OpenApiRouter($this->getFixtureFinder(), new LaravelRoutingAdapter($app = $this->getApp()), $options))
            ->registerRoutes();

        /** @var Router $router */
        $router = $app['router'];
        $this->assertNotNull($router->getRoutes()->getByName('getya'));

        $this->assertEquals($openapisCached, $cache ? $cache->has(OpenApiRouter::CACHE_KEY_OPENAPI) : false);
    }

    protected function getApp(): Application
    {
        $app = new Application();
        Facade::setFacadeApplication($app);

        return $app;
    }
}
