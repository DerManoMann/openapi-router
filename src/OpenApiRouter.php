<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing;

use OpenApi\Analysis;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;

/**
 * OpenApi router.
 */
class OpenApiRouter
{
    protected $sources = [];
    protected $routingAdapter;

    /**
     * Create new routes.
     *
     * @param array                   $sources        Mixed list of either controller paths or instances of `OpenApi\Annotations\OpenApi`
     * @param RoutingAdapterInterface $routingAdapter the framework adapter
     */
    public function __construct(array $sources, RoutingAdapterInterface $routingAdapter)
    {
        $this->sources = $sources;
        $this->routingAdapter = $routingAdapter;

        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        // provide default @OA\Info in case we need to do some scanning
        $analysis = new Analysis([new Info(['title' => 'Test', 'version' => '1.0'])]);
        $options = [
            'analysis' => $analysis,
        ];
        foreach ($this->sources as $source) {
            $openapi = is_string($source) ? \OpenApi\scan($source, $options) : $source;

            if (!($openapi instanceof OpenApi)) {
                throw new \InvalidArgumentException(sprintf('Invalid source. Expecting path (string) or "OpenApi\Annotations\OpenApi"'));
            }

            $this->registerOpenApi($openapi);
        }
    }

    protected function registerOpenApi(OpenApi $openapi)
    {
        $methods = ['get', 'post', 'put', 'patch', 'delete', 'options', 'head'];
        $xKeys = ['name'];

        foreach ($openapi->paths as $path) {
            $operation = null;
            /** @var Parameter[] $parameters */
            $parameters = [];

            foreach ($methods as $method) {
                if (\OpenApi\UNDEFINED !== $path->{$method}) {
                    /** @var Operation $operation */
                    $operation = $path->{$method};

                    if (\OpenApi\UNDEFINED !== $operation->parameters) {
                        foreach ($operation->parameters as $parameter) {
                            if ('path' == $parameter->in) {
                                $parameters[] = $parameter;
                            }
                        }
                    }

                    break;
                }
            }

            if ($operation) {
                $custom = [
                    'name' => null,
                ];
                if (\OpenApi\UNDEFINED !== $operation->x) {
                    foreach ($xKeys as $xKey) {
                        if (array_key_exists($xKey, $operation->x)) {
                            $custom[$xKey] = $operation->x[$xKey];
                        }
                    }
                }

                $this->routingAdapter->register($operation, $parameters, $custom);
            }
        }
    }
}
