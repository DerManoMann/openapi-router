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
            'no-cache-reload' => [null, true, false],
            'no-cache-no-reload' => [null, false, false],
            'cache-reload' => [new Psr16Cache(new ArrayAdapter()), false, true],
            'cache-no-reload' => [new Psr16Cache(new ArrayAdapter()), true, false],
        ];
    }

    /**
     * @dataProvider reloadTests
     */
    public function testReload(?CacheInterface $cache, bool $reload, bool $openapisCached): void
    {
        $options = [
            OpenApiRouter::OPTION_OA_INFO_INJECT => true,
            OpenApiRouter::OPTION_RELOAD => $reload,
            OpenApiRouter::OPTION_CACHE => $cache,
        ];

        (new OpenApiRouter($this->getFixtureFinder(), new LaravelRoutingAdapter($app = $this->getApp()), $options))
            ->registerRoutes();

        /** @var Router $router */
        $router = $app['router'];
        $this->assertNotNull($router->getRoutes()->getByName('getya'));

        $this->assertEquals($openapisCached, $cache && $cache->has(OpenApiRouter::CACHE_KEY_OPENAPI));
    }

    protected function getApp(): Application
    {
        $app = new Application();
        Facade::setFacadeApplication($app);

        return $app;
    }
}
