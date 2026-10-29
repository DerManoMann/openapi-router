<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Laravel;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use Radebatz\OpenApi\Routing\Adapters\LaravelRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Radebatz\OpenApi\Routing\Tests\Concerns\Fixtures;

trait CallsApplicationTrait
{
    use Fixtures;

    protected $app;

    protected function setUp(): void
    {
        if (!class_exists(Application::class)) {
            $this->markTestSkipped('not installed.');
        }

        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    public function createApplication()
    {
        if (!$this->app) {
            // a bare application; the base path only needs somewhere to write its bootstrap cache
            $basePath = sys_get_temp_dir() . '/openapi-router-laravel';
            if (!is_dir($basePath . '/bootstrap/cache')) {
                mkdir($basePath . '/bootstrap/cache', 0777, true);
            }
            $app = Application::configure(basePath: $basePath)->create();
            $app->make(Kernel::class)->bootstrap();
            $app['config']->set([
                'app.environment' => 'local',
                'app.debug' => true,
            ]);
            Facade::setFacadeApplication($app);

            (new OpenApiRouter($this->getFixtureFinder(), new LaravelRoutingAdapter($app)))
                ->registerRoutes();

            $this->app = $app;
        }

        return $this->app;
    }

    protected function getRouter(?Application $app = null): Router
    {
        $app = $app ?: $this->app;

        return $app['router'];
    }

    /**
     * @param array<string,mixed>|string $parameters
     */
    protected function route(string $name, array|string $parameters = [], bool $absolute = true): string
    {
        return $this->createApplication()['url']->route($name, $parameters, $absolute);
    }
}
