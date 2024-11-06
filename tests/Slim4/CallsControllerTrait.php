<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Slim4;

use Nyholm\Psr7\Factory\Psr17Factory;
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
        if (!class_exists('\\Slim\\App') || version_compare(App::VERSION, '4.0.0', '<')) {
            $this->markTestSkipped('not installed.');
        }
    }

    protected function getRouteCollector(?App $app = null): RouteCollectorInterface
    {
        $app = $app ?: $this->getApp();

        return $app->getRouteCollector();
    }

    protected function getApp(): App
    {
        $app = AppFactory::create();

        $options = [
            OpenApiRouter::OPTION_OA_INFO_INJECT => true,
        ];
        (new OpenApiRouter($this->getFixtureFinder(), new SlimRoutingAdapter($app), $options))
            ->registerRoutes();
        $openapi = (new OpenApiRouter($this->getFixtureFinder(), new SlimRoutingAdapter($app), $options))
            ->scan();
        file_put_contents(__DIR__ . '/openapi.yaml', $openapi->toYaml());

        return $app;
    }

    protected function call($path, $method = 'GET'): ResponseInterface
    {
        $request = (new Psr17Factory())->createServerRequest($method, $path);

        return $this->getApp()->handle($request);
    }
}
