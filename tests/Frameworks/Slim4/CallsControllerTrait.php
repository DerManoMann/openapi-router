<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Slim4;

use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Radebatz\OpenApi\Routing\Adapters\SlimRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteCollectorInterface;

trait CallsControllerTrait
{
    protected function setUp(): void
    {
        parent::setUp();
        if (version_compare('4.0', App::VERSION, '>=')) {
            $this->markTestSkipped('Slim3 detected, skipping Slim4 tests');
        }
    }

    protected function getRouteCollection(?App $app = null): RouteCollectorInterface
    {
        $app = $app ?: $this->getApp();

        return $app->getRouteCollector();
    }

    protected function getApp(): App
    {
        $app = AppFactory::create();

        (new OpenApiRouter([__DIR__ . '/../Fixtures'], new SlimRoutingAdapter($app)))
            ->registerRoutes();

        return $app;
    }

    protected function call($path, $method = 'GET', $silent = true): ResponseInterface
    {
        $app = $this->getApp();

        return $app->handle(new ServerRequest($method, $path));
    }
}
