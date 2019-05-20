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

    protected function getRouter(?App $app = null): RouterInterface
    {
        $app = $app ?: $this->getApp();

        (new OpenApiRouter([__DIR__ . '/Controllers/Slim'], new SlimRoutingAdapter($app)))
            ->registerRoutes();

        /** @var RouterInterface $router */
        $router = $app->getContainer()->get('router');

        return $router;
    }

    public function testNamedRoute()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('getya'));
        $this->assertEquals('/getya', $route->getPattern());
    }

    public function testParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('hey'));
        $this->assertEquals('/hey/{name}', $route->getPattern());
    }

    public function testOptionalParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('oi'));
        $this->assertEquals('/oi[/{name}]', $route->getPattern());
    }

    public function testMultiOptionalParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('multi'));
        $this->assertEquals('/multi[/{foo}[/{bar}]]', $route->getPattern());
    }

    public function testTypedParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('id'));
        $this->assertEquals('/id/{id:[0-9]+}', $route->getPattern());
    }

    public function testRegexParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('hid'));
        $this->assertEquals('/hid/{hid:[0-9a-f]+}', $route->getPattern());
    }

    public function testMiddlewares()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('mw'));
        $this->assertEquals('/mw', $route->getPattern());
        $this->assertCount(2, $route->getMiddleware());
    }
}
