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
                $path = str_replace("/{{$name}}", "[/{{$name}}]", $path);
            }

            if (\OpenApi\UNDEFINED !== $parameter->schema) {
                $schema = $parameter->schema;

                if (\OpenApi\UNDEFINED !== $schema->pattern) {
                    // TODO
                }
            }
        }

        $route = $this->app->map([strtoupper($operation->method)], $path, $operation->operationId);
        if ($custom['name']) {
            $route->setName($custom['name']);
        }
    }
}
