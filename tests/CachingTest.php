<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Routing\Adapters\LaravelRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Symfony\Component\Cache\Simple\ArrayCache;

class CachingTest extends TestCase
{
    public function revalidateTests()
    {
        return [
            [null, true, false],
            [null, false, false],
            [new ArrayCache(), false, true],
            [new ArrayCache(), true, false],
        ];
    }

    /**
     * @dataProvider revalidateTests
     */
    public function testRevalidate($cache, $revalidate, $openapisCached)
    {
        $options = [
            'revalidate' => $revalidate,
            'cache' => $cache,
        ];

        (new OpenApiRouter([__DIR__ . '/Controllers/Laravel'], new LaravelRoutingAdapter($app = $this->getApp()), $options))
            ->registerRoutes();

        /** @var Router $router */
        $router = $app['router'];
        $this->assertNotNull($router->getRoutes()->getByName('getya'));

        $this->assertEquals($openapisCached, $cache ? $cache->hasItem(OpenApiRouter::CACHE_KEY_OPENAPI) : false);
    }

    protected function getApp(): Application
    {
        $app = new Application();
        Facade::setFacadeApplication($app);

        return $app;
    }
}
