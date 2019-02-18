<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Routing\Adapters\SlimRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Slim\App;
use Slim\Interfaces\RouterInterface;

class SlimTest extends TestCase
{
    protected function getApp(): App
    {
        return new App();
    }

    public function testNamedRoute()
    {
        new OpenApiRouter([__DIR__ . '/Controllers/Slim'], new SlimRoutingAdapter($app = $this->getApp()));

        /** @var RouterInterface $router */
        $router = $app->getContainer()->get('router');
        $this->assertNotNull($router->getNamedRoute('getya'));
    }
}
