<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Silex;

use Radebatz\OpenApi\Routing\Adapters\SilexRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Silex\Application;
use Symfony\Component\Routing\RouteCollection;

trait CallsControllerTrait
{
    protected function getRouter(?Application $app = null): RouteCollection
    {
        $app = $app ?: $this->getApp();

        (new OpenApiRouter([__DIR__ . '/../Fixtures'], new SilexRoutingAdapter($app)))
            ->registerRoutes();

        $app->boot();
        $app->flush();

        /** @var RouteCollection $routes */
        $routes = $app['routes'];

        return $routes;
    }

    protected function getApp(): Application
    {
        $app = new Application();

        return $app;
    }
}
