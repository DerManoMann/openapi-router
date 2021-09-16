<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Laravel;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use Radebatz\OpenApi\Routing\Adapters\LaravelRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;

trait CallsApplicationTrait
{
    protected $app = null;

    protected function setUp(): void
    {
        if (!class_exists('\\Illuminate\\Foundation\\Application')) {
            $this->markTestSkipped('not installed.');
        }

        parent::setUp();
    }

    /** @inheritdoc */
    public function createApplication()
    {
        if (!$this->app) {
            $app = require __DIR__ . '/../../../vendor/laravel/laravel/bootstrap/app.php';
            $app->make(Kernel::class)->bootstrap();
            $app['config']->set([
                'app.environment' => 'local',
                'app.debug' => true,
            ]);
            Facade::setFacadeApplication($app);

            (new OpenApiRouter([__DIR__ . '/../Fixtures'], new LaravelRoutingAdapter($app)))
                ->registerRoutes();
            $openapi = (new OpenApiRouter([__DIR__ . '/../Fixtures'], new LaravelRoutingAdapter($app)))
                ->scan();
            //file_put_contents(__DIR__ . '/openapi.yaml', $openapi->toYaml());

            $this->app = $app;
        }

        return $this->app;
    }

    protected function getRouter(?Application $app = null): Router
    {
        $app = $app ?: $this->app;

        return $app['router'];
    }

    protected function route(string $name, $parameters = [], bool $absolute = true): string
    {
        return $this->createApplication()['url']->route($name, $parameters, $absolute);
    }
}
