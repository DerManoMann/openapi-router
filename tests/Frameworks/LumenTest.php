<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks;

use Laravel\Lumen\Application;
use Laravel\Lumen\Routing\Router;
use Laravel\Lumen\Testing\TestCase;
use Radebatz\OpenApi\Routing\Adapters\LumenRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;

class LumenTest extends TestCase
{
    /** @test */
    public function namedRoute()
    {
        /** @var Router $router */
        $router = $this->app->router;
        $this->assertTrue(array_key_exists('getya', $router->namedRoutes));
    }

    /** @test */
    public function getya()
    {
        $this->get('/getya/foo');
        //  fails for 5.7 $this->get(route('getya'));

        $this->assertEquals(200, $this->response->getStatusCode());
    }

    /** @inheritDoc */
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

        (new OpenApiRouter([__DIR__ . '/Fixtures/Lumen'], new LumenRoutingAdapter($app), $options))
            ->registerRoutes();

        return $app;
    }
}
