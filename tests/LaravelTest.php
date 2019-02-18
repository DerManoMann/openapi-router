<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Routing\Adapters\LaravelRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;

class LaravelTest extends TestCase
{
    protected function getApp(): Application
    {
        $app = new Application();
        Facade::setFacadeApplication($app);

        return $app;
    }

    public function testNamedRoute()
    {
        new OpenApiRouter([__DIR__ . '/Controllers/Laravel'], new LaravelRoutingAdapter($app = $this->getApp()));

        /** @var Router $router */
        $router = $app['router'];
        //var_dump($router);
        $this->assertNotNull($router->getRoutes()->getByName('getya'));
    }
}
