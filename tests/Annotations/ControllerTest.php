<?php

namespace Radebatz\OpenApi\Routing\Tests\Annotations;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use OpenApi\Annotations\OpenApi;
use Radebatz\OpenApi\Routing\Adapters\LaravelRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;

class ControllerTest extends TestCase
{
    /** @var OpenApi */
    protected $openapi = null;

    public function createApplication()
    {
        $app = require __DIR__ . '/../../vendor/laravel/laravel/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        config([
            'app.environment' => 'local',
            'app.debug' => true,
        ]);
        Facade::setFacadeApplication($app);

        $this->openapi = (new OpenApiRouter(__DIR__ . '/../Fixtures', new LaravelRoutingAdapter($app)))
            ->registerRoutes();

        return $app;
    }

    /** @test */
    public function prefix()
    {
        /** @var Router $router */
        $router = $this->app->get('router');

        $this->assertNotNull($getyaRoute = $router->getRoutes()->getByName('bar'));
        $this->assertEquals('foo/bar', $getyaRoute->uri);

        $this->assertNotNull($getyaRoute = $router->getRoutes()->getByName('getya'));
        $this->assertEquals('getya', $getyaRoute->uri);
    }

    public function middlewareTests()
    {
        return [
            ['bar', 1],
            ['barx', 1],
            ['getya', 1],
            ['barxx', 2],
        ];
    }

    /**
     * @test
     * @dataProvider middlewareTests
     */
    public function middleware($name, $expected)
    {
        /** @var Router $router */
        $router = $this->app->get('router');

        $route = $router->getRoutes()->getByName($name);

        $this->assertNotNull($route);
        $this->assertCount($expected, $route->middleware());
        $this->assertNotContains(' middleware', $this->openapi->toYaml());
    }

    /** @test */
    public function responses()
    {
        /** @var Router $router */
        $router = $this->app->get('router');

        $this->assertNotNull($this->openapi);

        foreach ($this->openapi->paths as $path => $pathItem) {
            if ('foo/bar' == $path) {
                $operation = $pathItem->get;
                $this->assertEquals('bar', $operation->operationId);
                $this->assertCount(2, $operation->responses);
                $this->assertEquals('200', $operation->responses[0]->response);
                $this->assertEquals('401', $operation->responses[1]->response);
            }
        }
    }
}
