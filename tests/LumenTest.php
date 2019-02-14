<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests;

use Laravel\Lumen\Application;
use Laravel\Lumen\Routing\Router;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Routing\Adapters\LumenRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;

class LumenTest extends TestCase
{
    protected function getApp(): Application
    {
        $app = new Application();

        return $app;
    }

    public function testNamedRoute()
    {
        new OpenApiRouter([__DIR__ . '/Controllers/Lumen'], new LumenRoutingAdapter($app = $this->getApp()));

        /** @var Router $router */
        $router = $app->router;
        $this->assertTrue(array_key_exists('getya', $router->namedRoutes));
    }
}