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
        if (!class_exists('\\Illuminate\\Foundation\\Application')) {
            $this->markTestSkipped('not installed.');
        }

        parent::setUp();
    }

    /** @inheritdoc */
    public function createApplication()
    {
        if (!$this->app) {
            $app = require __DIR__ . '/../../vendor/laravel/laravel/bootstrap/app.php';
            $app->make(Kernel::class)->bootstrap();
            $app['config']->set([
                'app.environment' => 'local',
                'app.debug' => true,
            ]);
            Facade::setFacadeApplication($app);

            $options = [
                OpenApiRouter::OPTION_OA_INFO_INJECT => true,
            ];
            (new OpenApiRouter($this->getFixtureFinder(), new LaravelRoutingAdapter($app), $options))
                ->registerRoutes();
            $openapi = (new OpenApiRouter($this->getFixtureFinder(), new LaravelRoutingAdapter($app), $options))
                ->scan();
            file_put_contents(__DIR__ . '/openapi.yaml', $openapi->toYaml());

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
