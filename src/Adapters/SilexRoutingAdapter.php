<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Adapters;

use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;
use Silex\Application;
use Silex\ControllerCollection;

/**
 * Silex routing adapter.
 */
class SilexRoutingAdapter implements RoutingAdapterInterface
{
    /** @var Application $app */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Operation $operation, array $parameters, array $custom)
    {
        $path = $operation->path;

        /** @var ControllerCollection $controllers */
        $controllers = $this->app['controllers'];

        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {
            $name = $parameter->name;

            if (!$parameter->required) {
                // TODO
            }

            if (\OpenApi\UNDEFINED !== $parameter->schema) {
                $schema = $parameter->schema;

                if (\OpenApi\UNDEFINED !== $schema->pattern) {
                    // TODO
                }
            }
        }

        $controller = $controllers->match($path, $operation->operationId)->method(strtoupper($operation->method));
        if ($custom['name']) {
            $controller->bind($custom['name']);
        }
    }
}
