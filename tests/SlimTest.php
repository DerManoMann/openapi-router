<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Routing\Adapters\SlimRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Interfaces\RouterInterface;

class SlimTest extends TestCase
{
    /** @test */
    public function namedRoute()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('getya'));
        $this->assertEquals('/getya', $route->getPattern());
    }

    /** @test */
    public function Parameter()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('hey'));
        $this->assertEquals('/hey/{name}', $route->getPattern());
    }

    /** @test */
    public function optionalParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('oi'));
        $this->assertEquals('/oi[/{name}]', $route->getPattern());
    }

    /** @test */
    public function multiOptionalParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('multi'));
        $this->assertEquals('/multi[/{foo}[/{bar}]]', $route->getPattern());
    }

    /** @test */
    public function typedParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('id'));
        $this->assertEquals('/id/{id:[0-9]+}', $route->getPattern());
    }

    /** @test */
    public function regexParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('hid'));
        $this->assertEquals('/hid/{hid:[0-9a-f]+}', $route->getPattern());
    }

    /** @test */
    public function middlewares()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('mw'));
        $this->assertEquals('/mw', $route->getPattern());
        $this->assertCount(2, $route->getMiddleware());
    }

    /** @test */
    public function request()
    {
        $response = $this->call('/getya');

        $this->assertEquals(200, $response->getStatusCode());
    }

    protected function getRouter(?App $app = null): RouterInterface
    {
        $app = $app ?: $this->getApp();

        /** @var RouterInterface $router */
        $router = $app->getContainer()->get('router');

        return $router;
    }

    protected function getApp(): App
    {
        $app = new App();

        (new OpenApiRouter([__DIR__ . '/Controllers/Slim'], new SlimRoutingAdapter($app)))
            ->registerRoutes();

        return $app;
    }

    protected function call($path, $method = 'GET'): Response
    {
        $environment = Environment::mock([
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => $path,
        ]);

        $app = $this->getApp();
        $app->getContainer()['request'] = Request::createFromEnvironment($environment);

        return $app->run(true);
    }
}
