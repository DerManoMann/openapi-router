<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Adapters;

use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;
use Slim\App;

/**
 * Slim routing adapter.
 */
class SlimRoutingAdapter implements RoutingAdapterInterface
{
    /** @var App $app */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Operation $operation, array $parameters, array $custom)
    {
        $path = $operation->path;

        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {
            $name = $parameter->name;

            if (!$parameter->required) {
                if (false !== strpos($path, $needle = "/{{$name}}[/{")) {
                    // multiple optional parameters
                    $path = preg_replace("#/{{$name}}(\[?.*}\])#", "[/{{$name}}$1]", $path);
                } else {
                    $path = str_replace("/{{$name}}", "[/{{$name}}]", $path);
                }
            }

            if (\OpenApi\UNDEFINED !== $parameter->schema) {
                $schema = $parameter->schema;
                switch ($schema->type) {
                    case 'string':
                        if (\OpenApi\UNDEFINED !== ($pattern = $schema->pattern)) {
                            $path = str_replace("{{$name}}", "{{$name}:$pattern}", $path);
                        }
                        break;
                    case 'integer':
                        $path = str_replace("{{$name}}", "{{$name}:[0-9]+}", $path);
                        break;
                }
            }
        }

        $route = $this->app->map([strtoupper($operation->method)], $path, $operation->operationId);
        if ($custom[static::X_NAME]) {
            $route->setName($custom[static::X_NAME]);
        }
        foreach ($custom[static::X_MIDDLEWARE] as $middleware) {
            $route->add($middleware);
        }
    }
}
