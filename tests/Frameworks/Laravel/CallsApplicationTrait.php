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

    /** {@inheritdoc} */
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

            $this->app = $app;
        }

        return $this->app;
    }

    protected function getRouter(?Application $application = null): Router
    {
        return $this->createApplication()['router'];
    }

    protected function route($name, $parameters = [], $absolute = true)
    {
        return $this->createApplication()['url']->route($name, $parameters, $absolute);
    }
}
