<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Adapters;

use Radebatz\OpenApi\Routing\RouteRegistration;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;
use Slim\App;

/**
 * Slim routing adapter.
 */
class SlimRoutingAdapter implements RoutingAdapterInterface
{
    protected App $app;
    protected array $options;

    public function __construct(App $app, array $options = [])
    {
        $this->app = $app;
        $this->options = array_merge([
            static::OPTION_AUTO_REGEX => true,
        ], $options);
    }

    /**
     * @inheritdoc
     */
    public function register(RouteRegistration $route): void
    {
        $path = $route->path;

        $controller = str_replace('::', ':', $route->controller);

        foreach ($route->parameters as $name => $parameter) {
            if (!$parameter['required']) {
                if (false !== strpos($path, $needle = "/{{$name}}[/{")) {
                    // multiple optional parameters
                    $path = preg_replace("#/{{$name}}(\[?.*}\])#", "[/{{$name}}$1]", $path);
                } else {
                    $path = str_replace("/{{$name}}", "[/{{$name}}]", $path);
                }
            }

            switch ($parameter['type']) {
                case 'regex':
                    if ($pattern = $parameter['pattern']) {
                        $path = str_replace("{{$name}}", "{{$name}:$pattern}", $path);
                    }
                    break;

                case 'integer':
                    if ($this->options[static::OPTION_AUTO_REGEX]) {
                        $path = str_replace("{{$name}}", "{{$name}:[0-9]+}", $path);
                    }
                    break;
            }
        }

        $slimRoute = $this->app->map([strtoupper($route->method)], $path, $controller);
        if ($route->custom[static::X_NAME]) {
            $slimRoute->setName($route->custom[static::X_NAME]);
        }

        foreach ($route->custom[static::X_MIDDLEWARE] as $middleware) {
            $slimRoute->add($middleware);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerCached(): bool
    {
        return false;
    }
}
