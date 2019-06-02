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

    protected function getRouter(?Application $app = null): RouteCollection
    {
        $app = $app ?: $this->getApp();

        (new OpenApiRouter([__DIR__ . '/Controllers/Silex'], new SilexRoutingAdapter($app)))
            ->registerRoutes();

        $app->boot();
        $app->flush();

        /** @var RouteCollection $routes */
        $routes = $app['routes'];

        return $routes;
    }

    public function testNamedRoute()
    {
        $this->assertNotNull($route = $this->getRouter()->get('getya'));
        $this->assertEquals('/getya', $route->getPath());
    }

    public function testParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->get('hey'));
        $this->assertEquals('/hey/{name}', $route->getPath());
    }

    public function testTypedParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->get('id'));
        $this->assertEquals(['id' => '[0-9]+'], $route->getRequirements());
    }

    public function testRegexParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->get('hid'));
        $this->assertEquals(['hid' => '[0-9a-f]+'], $route->getRequirements());
    }
}
