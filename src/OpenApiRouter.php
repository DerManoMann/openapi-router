<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing;

use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Analysers\DocBlockAnnotationFactory;
use OpenApi\Analysers\ReflectionAnalyser;
use OpenApi\Analysis;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\PathItem;
use OpenApi\Context;
use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use Psr\SimpleCache\CacheInterface;
use Radebatz\OpenApi\Routing\Annotations as OAX;
use Radebatz\OpenApi\Routing\Annotations\MiddlewareProperty;
use Radebatz\OpenApi\Routing\Processors\ControllerCleanup;
use Radebatz\OpenApi\Routing\Processors\MergeController;
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
                self::OPTION_OA_OPERATION_ID_AS_NAME => true,
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

        $this->registerOpenApi($openapi ?: ($openapi = $this->scan()));

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

                if (Generator::UNDEFINED !== $pathItem->{$method}) {
                    /** @var Operation $operation */
                    $operation = $pathItem->{$method};

                    if (Generator::UNDEFINED !== $operation->parameters) {
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

                        $middleware = [];
                        $uses = array_flip(class_uses($operation));
                        if (array_key_exists(MiddlewareProperty::class, $uses)) {
                            if (Generator::UNDEFINED !== $operation->middleware && is_array($operation->middleware)) {
                                $middleware = $operation->middleware;
                            }
                        }

                        $custom = [
                            RoutingAdapterInterface::X_NAME => $this->options[self::OPTION_OA_OPERATION_ID_AS_NAME] ? $operation->operationId : null,
                            RoutingAdapterInterface::X_MIDDLEWARE => $middleware,
                        ];
                        if (Generator::UNDEFINED !== $operation->x) {
                            foreach (array_keys($custom) as $xKey) {
                                if (array_key_exists($xKey, $operation->x)) {
                                    if (is_array($custom[$xKey])) {
                                        $custom[$xKey] = array_merge($custom[$xKey], $operation->x[$xKey]);
                                    } else {
                                        $custom[$xKey] = $operation->x[$xKey];
                                    }
                                }
                            }
                        }

                        $this->routingAdapter->register(
                            $operation,
                            $controller,
                            $this->parameterMetadata(array_reverse($parameters)),
                            $custom
                        );
                    }
                }
            }
        }
    }

    /**
     * Extract (uri) parameter meta data.
     *
     * @param Parameter[] $parameters
     */
    protected function parameterMetadata($parameters): array
    {
        $metadata = [];

        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {
            $name = $parameter->name;

            $metadata[$name] = [
                'required' => Generator::UNDEFINED !== $parameter->required ? $parameter->required : false,
                'type' => null,
                'pattern' => null,
            ];

            if (Generator::UNDEFINED !== $parameter->schema) {
                $schema = $parameter->schema;
                switch ($schema->type) {
                    case 'string':
                        $metadata[$name]['type'] = $schema->type;
                        if (Generator::UNDEFINED !== ($pattern = $schema->pattern)) {
                            $metadata[$name]['type'] = 'regex';
                            $metadata[$name]['pattern'] = $schema->pattern;
                        }
                        break;
                    case 'integer':
                        $metadata[$name]['type'] = $schema->type;
                        break;
                }
            }
        }

        return $metadata;
    }

    public function scan(): OpenApi
    {
        // provide default @OA\Info in case we need to do some scanning
        $analysis = $this->options[self::OPTION_OA_INFO_INJECT]
            ? new Analysis([new Info(['title' => 'Test', 'version' => '1.0'])], new Context())
            : null;

        return $this->generator()
            ->setAnalyser(new ReflectionAnalyser([new DocBlockAnnotationFactory(), new AttributeAnnotationFactory()]))
            ->generate($this->sources, $analysis);
    }

    /**
     * Set up Generator.
     *
     * Registers our custom annotations under the `oax` namespace alias.
     */
    public function generator(): Generator
    {
        $operations = [
            OAX\Get::class => 'get',
            OAX\Post::class => 'post',
            OAX\Put::class => 'put',
            OAX\Patch::class => 'patch',
            OAX\Delete::class => 'delete',
            OAX\Options::class => 'options',
            OAX\Head::class => 'head',
            OAX\Trace::class => 'trace',
        ];
        foreach ($operations as $class => $operation) {
            PathItem::$_nested[$class] = $operation;
            $class::$_blacklist[] = 'middleware';
        }

        $routingNamespace = 'Radebatz\\OpenApi\\Routing\\Annotations';
        $generator = (new Generator())
            ->addNamespace($routingNamespace . '\\')
            ->setAliases(['oax' => $routingNamespace])
            ->addProcessor(new ControllerCleanup());

        $processors = $generator->getProcessors();
        $insertMergeController = function (array $processors) {
            $tmp = [];
            foreach ($processors as $processor) {
                if (get_class($processor) == BuildPaths::class) {
                    $tmp[] = new MergeController();
                }
                $tmp[] = $processor;
            }

            return $tmp;
        };
        $generator->setProcessors($insertMergeController($processors));

        return $generator;
    }
}
