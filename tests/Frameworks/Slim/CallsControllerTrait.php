<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Slim;

use Radebatz\OpenApi\Routing\Adapters\SlimRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Interfaces\RouterInterface;

trait CallsControllerTrait
{
    protected function getRouter(?App $app = null): RouterInterface
    {
        $app = $app ?: $this->getApp();

        /** @var RouterInterface $router */
        $router = $app->getContainer()->get('router');

        return $router;
    }

    protected function getApp(): App
    {
        $app = new App();

        (new OpenApiRouter([__DIR__ . '/../Fixtures'], new SlimRoutingAdapter($app)))
            ->registerRoutes();

        return $app;
    }

    protected function call($path, $method = 'GET', $silent = true): Response
    {
        $environment = Environment::mock([
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => $path,
        ]);

        $app = $this->getApp();
        $app->getContainer()['request'] = Request::createFromEnvironment($environment);

        return $app->run($silent);
    }
}
