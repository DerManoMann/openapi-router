<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Lumen;

use Laravel\Lumen\Application;
use Laravel\Lumen\Routing\Router;
use Radebatz\OpenApi\Routing\Adapters\LumenRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Radebatz\OpenApi\Routing\Tests\Concerns\Fixtures;
use function app;

trait CallsApplicationTrait
{
    use Fixtures;

    protected function setUp(): void
    {
        if (!class_exists('\\Laravel\\Lumen\\Application')) {
            $this->markTestSkipped('not installed.');
        }
        parent::setUp();
    }

    /** @inheritdoc */
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

        (new OpenApiRouter($this->getFixtureFinder(), new LumenRoutingAdapter($app), $options))
            ->registerRoutes();
        $openapi = (new OpenApiRouter($this->getFixtureFinder(), new LumenRoutingAdapter($app), $options))
            ->scan();
        file_put_contents(__DIR__ . '/openapi.yaml', $openapi->toYaml());

        return $app;
    }

    protected function getRouter(?Application $app = null): Router
    {
        $app = $app ?: $this->app;

        return $app->router;
    }

    public function route($name, $parameters = [], $secure = null)
    {
        return $this->app['url']->route($name, $parameters, $secure);
    }
}
