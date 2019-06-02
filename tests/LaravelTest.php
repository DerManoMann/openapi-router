<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use Radebatz\OpenApi\Routing\Adapters\LaravelRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;

class LaravelTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        config(['app.environment' => 'local', 'app.debug' => true]);
        Facade::setFacadeApplication($app);

        (new OpenApiRouter([__DIR__ . '/Controllers/Laravel'], new LaravelRoutingAdapter($app)))
            ->registerRoutes();

        return $app;
    }

    /** @test */
    public function namedRoute()
    {
        $app = $this->createApplication();

        /** @var Router $router */
        $router = $app['router'];

        $this->assertNotNull($router->getRoutes()->getByName('getya'));
    }

    /** @test */
    public function getya()
    {
        $response = $this->get(route('getya'));

        $response->assertStatus(200);
    }
}
