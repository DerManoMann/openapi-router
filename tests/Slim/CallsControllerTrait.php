<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Slim;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Radebatz\OpenApi\Routing\Adapters\SlimRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Radebatz\OpenApi\Routing\Tests\Concerns\Fixtures;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteCollectorInterface;

trait CallsControllerTrait
{
    use Fixtures;

    protected function setUp(): void
    {
        if (!class_exists(App::class) || version_compare(App::VERSION, '4.0.0', '<')) {
            $this->markTestSkipped('not installed.');
        }
    }

    /**
     * @param App<ContainerInterface|null>|null $app
     */
    protected function getRouteCollector(?App $app = null): RouteCollectorInterface
    {
        $app = $app ?: $this->getApp();

        return $app->getRouteCollector();
    }

    /**
     * @return App<ContainerInterface|null>
     */
    protected function getApp(): App
    {
        $app = AppFactory::create();

        (new OpenApiRouter($this->getFixtureFinder(), new SlimRoutingAdapter($app)))
            ->registerRoutes();

        return $app;
    }

    protected function call(string $path, string $method = 'GET'): ResponseInterface
    {
        $request = (new Psr17Factory())->createServerRequest($method, $path);

        return $this->getApp()->handle($request);
    }
}
