<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Routing\Adapters\SilexRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Silex\Application;
use Symfony\Component\Routing\RouteCollection;

class SilexTest extends TestCase
{
    protected function getApp(): Application
    {
        $app = new Application();

        return $app;
    }

    public function testNamedRoute()
    {
        new OpenApiRouter([__DIR__ . '/Controllers/Silex'], new SilexRoutingAdapter($app = $this->getApp()));
        $app->boot();
        $app->flush();

        /** @var RouteCollection $routes */
        $routes = $app['routes'];
        $this->assertNotNull($routes->get('getya'));
    }
}
