<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Lumen;

use Laravel\Lumen\Application;
use Laravel\Lumen\Routing\Router;
use Radebatz\OpenApi\Routing\Adapters\LumenRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;

trait CallsApplicationTrait
{
    /** {@inheritdoc} */
    public function createApplication()
    {
        $app = new Application();
        app('config')->set([
            'app.environment' => 'local',
            'app.debug' => true,
        ]);

        $options = [
            OpenApiRouter::OPTION_OA_INFO_INJECT => true,
            OpenApiRouter::OPTION_OA_OPERATION_ID_AS_NAME => true,
        ];

        (new OpenApiRouter([__DIR__ . '/../Fixtures'], new LumenRoutingAdapter($app), $options))
            ->registerRoutes();

        return $app;
    }

    protected function getRouter(?Application $application = null): Router
    {
        return $this->app->router;
    }

    public function route($name, $parameters = [], $secure = null)
    {
        $uri = $this->app['url']->route($name, $parameters, $secure);

        // 5.7 fixes
        $uri = str_replace('://:/', '://localhost/', $uri);

        return $uri;
    }
}
