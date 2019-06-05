<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests;

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
        $app = $this->createApplication();

        /** @var Router $router */
        $router = $app->router;
        $this->assertTrue(array_key_exists('getya', $router->namedRoutes));
    }

    /** @test */
    public function getya()
    {
        $this->get('/getya');
        //  fails for 5.7 $this->get(route('getya'));

        $this->assertEquals(200, $this->response->getStatusCode());
    }

    public function createApplication()
    {
        $app = new Application();

        $options = [
            OpenApiRouter::OPTION_OA_INFO_INJECT => true,
            OpenApiRouter::OPTION_OA_OPERATION_ID_AS_NAME => true,
        ];

        (new OpenApiRouter([__DIR__ . '/Controllers/Lumen'], new LumenRoutingAdapter($app), $options))
            ->registerRoutes();

        return $app;
    }
}
