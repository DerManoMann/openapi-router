<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Adapters;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Radebatz\OpenApi\Routing\RouteRegistration;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;

/**
 * Laravel routing adapter.
 */
class LaravelRoutingAdapter implements RoutingAdapterInterface
{
    /**
     * @param bool $autoRegex Constrain an `integer`-typed path parameter to `[0-9]+`
     */
    public function __construct(
        protected Application $app,
        protected bool $autoRegex = true,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function register(RouteRegistration $route): void
    {
        $path = $route->path;

        $where = [];
        foreach ($route->parameters as $name => $parameter) {
            if (!$parameter['required'] && str_contains($path, $needle = "/{{$name}}")) {
                $path = str_replace($needle, "/{{$name}?}", $path);
            }

            switch ($parameter['type']) {
                case 'regex':
                    if ($pattern = $parameter['pattern']) {
                        $where[$name] = $pattern;
                    }
                    break;

                case 'integer':
                    if ($this->autoRegex) {
                        $where[$name] = '[0-9]+';
                    }
                    break;
            }
        }

        // anchored: only a trailing `::__invoke` is the single-action form
        $controller = str_ends_with($route->controller, '::__invoke')
            ? substr($route->controller, 0, -strlen('::__invoke'))
            : $route->controller;

        /** @var Router $router */
        $router = $this->app->get('router');

        $action = [
            'uses' => str_replace('::', '@', $controller),
        ];
        if ($route->custom[static::X_NAME]) {
            $action['as'] = $route->custom[static::X_NAME];
        }

        // not chained: Route::middleware() returns the middleware list when called without
        // arguments, so its return type is `$this|array` and chaining off it is not safe
        $laravelRoute = $router->addRoute(strtoupper($route->method), $path, $action);
        $laravelRoute->middleware($route->custom[static::X_MIDDLEWARE]);
        $laravelRoute->where($where);
    }

    /**
     * @inheritdoc
     */
    public function registerCached(): bool
    {
        return false;
    }
}
