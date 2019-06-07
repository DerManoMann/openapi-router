<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing;

use OpenApi\Analyser;
use OpenApi\Analysis;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;
use Psr\SimpleCache\CacheInterface;
use Radebatz\OpenApi\Routing\Processors\ControllerProcessor;
use Symfony\Component\Finder\Finder;

/**
 * OpenApi router.
 */
class OpenApiRouter
{
    public const OPTION_RELOAD = 'relaod';
    public const OPTION_CACHE = 'cache';
    public const OPTION_OA_INFO_INJECT = 'oa_info_inject';
    public const OPTION_OA_OPERATION_ID_AS_NAME = 'oa_operation_id_as_name';

    public const CACHE_KEY_OPENAPI = 'openapi-router.openapi';

    protected $sources = [];
    protected $openapi = null;
    protected $routingAdapter;
    protected $options = [];

    /**
     * Create new routes.
     *
     * @param string|array|Finder     $sources        The directory(s) or filename(s)
     * @param RoutingAdapterInterface $routingAdapter the framework adapter
     * @param array                   $options        Optional configuration options
     */
    public function __construct($sources, RoutingAdapterInterface $routingAdapter, array $options = [])
    {
        $this->sources = $sources;
        $this->routingAdapter = $routingAdapter;
        $this->options = $options + [
                self::OPTION_RELOAD => true,
                self::OPTION_CACHE => null,
                self::OPTION_OA_INFO_INJECT => true,
                self::OPTION_OA_OPERATION_ID_AS_NAME => false,
            ];
    }

    public function registerRoutes(): ?OpenApi
    {
        if (!$this->options[self::OPTION_RELOAD] && $this->routingAdapter->registerCached()) {
            return null;
        }

        $openapi = null;
        /** @var CacheInterface $cache */
        if (($cache = $this->options[self::OPTION_CACHE]) && !$this->options[self::OPTION_RELOAD]) {
            // try cache
            $openapi = $cache->get(self::CACHE_KEY_OPENAPI);
        }

        $this->registerOpenApi($openapi ? : ($openapi = $this->scan()));

        if ($cache && !$this->options[self::OPTION_RELOAD]) {
            $cache->set(self::CACHE_KEY_OPENAPI, $openapi);
        }

        return $openapi;
    }

    protected function registerOpenApi(OpenApi $openapi)
    {
        $methods = ['get', 'post', 'put', 'patch', 'delete', 'options', 'head'];

        foreach ($openapi->paths as $pathItem) {
            foreach ($methods as $method) {
                $operation = null;
                /** @var Parameter[] $parameters */
                $parameters = [];

                if (\OpenApi\UNDEFINED !== $pathItem->{$method}) {
                    /** @var Operation $operation */
                    $operation = $pathItem->{$method};

                    if (\OpenApi\UNDEFINED !== $operation->parameters) {
                        foreach ($operation->parameters as $parameter) {
                            if ('path' == $parameter->in) {
                                $parameters[] = $parameter;
                            }
                        }
                    }

                    if ($operation) {
                        $controller = null;
                        $context = $operation->_context;
                        // as per \OpenApi\Processors\OperationId
                        if ($context && $context->method) {
                            if ($context->class) {
                                if ($context->namespace) {
                                    $controller = $context->namespace . '\\' . $context->class . '::' . $context->method;
                                } else {
                                    $controller = $context->class . '::' . $context->method;
                                }
                            } else {
                                $controller = $context->method;
                            }
                        }

                        $custom = [
                            RoutingAdapterInterface::X_NAME => $this->options[self::OPTION_OA_OPERATION_ID_AS_NAME] ? $operation->operationId : null,
                            RoutingAdapterInterface::X_MIDDLEWARE => [],
                        ];
                        if (\OpenApi\UNDEFINED !== $operation->x) {
                            foreach (array_keys($custom) as $xKey) {
                                if (array_key_exists($xKey, $operation->x)) {
                                    $custom[$xKey] = $operation->x[$xKey];
                                }
                            }
                        }

                        $this->routingAdapter->register($operation, $controller, array_reverse($parameters), $custom);
                    }
                }
            }
        }
    }

    public function scan(): OpenApi
    {
        // provide default @OA\Info in case we need to do some scanning
        $options = [
            'analysis' => new Analysis([new Info(['title' => 'Test', 'version' => '1.0'])]),
        ];

        Analyser::$whitelist[] = $ns = 'Radebatz\OpenApi\Routing\Annotations';
        Analyser::$defaultImports['oax'] = $ns;
        Analysis::registerProcessor($controllerProcessor = new ControllerProcessor());

        $openapi = \OpenApi\scan($this->sources, $this->options[self::OPTION_OA_INFO_INJECT] ? $options : []);

        Analysis::unregisterProcessor($controllerProcessor);
        unset(Analyser::$defaultImports['oax']);
        Analyser::$whitelist = array_filter(Analyser::$whitelist, function ($value) use ($ns) {
            return $value !== $ns;
        });

        return $openapi;
    }
}
