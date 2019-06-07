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

    public function __construct(Application $app, array $options = [])
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Operation $operation, string $controller, array $parameters, array $custom): void
    {
        $path = $operation->path;

        /** @var ControllerCollection $controllers */
        $controllers = $this->app['controllers'];

        $controller = $controllers->match($path, $controller)->method(strtoupper($operation->method));

        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {
            $name = $parameter->name;

            if (!$parameter->required) {
                // TODO
            }

            if (\OpenApi\UNDEFINED !== $parameter->schema) {
                $schema = $parameter->schema;
                switch ($schema->type) {
                    case 'string':
                        if (\OpenApi\UNDEFINED !== ($pattern = $schema->pattern)) {
                            $controller->assert($name, $pattern);
                        }
                        break;
                    case 'integer':
                        $controller->assert($name, '[0-9]+');
                        break;
                }
            }
        }

        if ($custom[static::X_NAME]) {
            $controller->bind($custom[static::X_NAME]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerCached(): bool
    {
        return false;
    }
}
