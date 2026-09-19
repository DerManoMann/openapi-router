<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing;

use OpenApi\Builder;
use OpenApi\Builder\Mode;
use OpenApi\Builder\Result;
use OpenApi\Spec as OA;
use OpenApi\Specification;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Radebatz\OpenApi\Routing\Attributes\Middleware;
use Symfony\Component\Finder\Finder;

/**
 * OpenApi router.
 */
class OpenApiRouter
{
    public const OPTION_RELOAD = 'reload';
    public const OPTION_CACHE = 'cache';
    public const OPTION_OA_OPERATION_ID_AS_NAME = 'oa_operation_id_as_name';

    public const CACHE_KEY_ROUTES = 'openapi-router.routes';

    protected string|array|Finder $sources;
    protected RoutingAdapterInterface $routingAdapter;
    protected array $options;

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
                self::OPTION_OA_OPERATION_ID_AS_NAME => true,
            ];
    }

    /**
     * @return list<RouteRegistration>|null `null` when the adapter's own route cache was used instead
     */
    public function registerRoutes(): ?array
    {
        if (!$this->options[self::OPTION_RELOAD] && $this->routingAdapter->registerCached()) {
            return null;
        }

        $routes = null;
        /** @var CacheInterface $cache */
        if (($cache = $this->options[self::OPTION_CACHE]) && !$this->options[self::OPTION_RELOAD]) {
            $routes = $cache->get(self::CACHE_KEY_ROUTES);
        }

        $routes ??= $this->extractRoutes($this->scan()->specification() ?? new Specification());

        foreach ($routes as $route) {
            $this->routingAdapter->register($route);
        }

        if ($cache && !$this->options[self::OPTION_RELOAD]) {
            $cache->set(self::CACHE_KEY_ROUTES, $routes);
        }

        return $routes;
    }

    /**
     * @return list<RouteRegistration>
     */
    protected function extractRoutes(Specification $specification): array
    {
        $classToPathItem = [];
        foreach ($specification->pathItems as $pathItem) {
            if (($className = $pathItem->getClassName()) !== null) {
                $classToPathItem[$className] = $pathItem;
            }
        }

        $routes = [];
        foreach ($specification->operations as $operation) {
            $reflector = $operation->getReflector();
            if (!$reflector instanceof \ReflectionMethod) {
                // no declaring method to dispatch to (e.g. a plain function) — nothing to register
                continue;
            }

            $controller = $operation->getClassName() . '::' . $reflector->getName();

            $parameters = [];
            foreach ($operation->parameters ?? [] as $parameter) {
                if ('path' === $parameter->in) {
                    $parameters[] = $parameter;
                }
            }

            $routes[] = new RouteRegistration(
                path: $operation->path ?? '',
                method: strtoupper($operation->method ?? 'GET'),
                controller: $controller,
                parameters: $this->parameterMetadata(array_reverse($parameters)),
                custom: $this->customProperties($operation, $classToPathItem),
            );
        }

        return $routes;
    }

    /**
     * @param array<class-string, OA\PathItem> $classToPathItem
     *
     * @return array<string,mixed>
     */
    protected function customProperties(OA\Operation $operation, array $classToPathItem): array
    {
        // PathItem's own class-level attachables don't clone down to its operations the way
        // tags/security/responses do (swagger-php's `PathItems` augmenter only clones those
        // three) — resolved here instead, so a controller-level `#[Middleware]` still applies
        // to every operation under it.
        $attachables = $operation->attachables ?? [];
        $className = $operation->getClassName();
        if ($className !== null && isset($classToPathItem[$className])) {
            $attachables = [...$classToPathItem[$className]->attachables ?? [], ...$attachables];
        }

        $middleware = [];
        foreach ($attachables as $attachable) {
            if ($attachable instanceof Middleware) {
                $middleware = array_merge($middleware, $attachable->names);
            }
        }

        $custom = [
            RoutingAdapterInterface::X_NAME => $this->options[self::OPTION_OA_OPERATION_ID_AS_NAME] ? $operation->operationId : null,
            RoutingAdapterInterface::X_MIDDLEWARE => array_values(array_unique($middleware)),
        ];

        foreach (array_keys($custom) as $xKey) {
            if (array_key_exists($xKey, $operation->x ?? [])) {
                $custom[$xKey] = is_array($custom[$xKey])
                    ? array_merge($custom[$xKey], (array) $operation->x[$xKey])
                    : $operation->x[$xKey];
            }
        }

        return $custom;
    }

    /**
     * Extract (uri) parameter meta data.
     *
     * @param list<OA\Parameter> $parameters
     *
     * @return array<string,array{required: bool, type: ?string, pattern: ?string}>
     */
    protected function parameterMetadata(array $parameters): array
    {
        $metadata = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->name;

            $metadata[$name] = [
                'required' => (bool) $parameter->required,
                'type' => null,
                'pattern' => null,
            ];

            if ($schema = $parameter->schema) {
                switch ($schema->type) {
                    case 'string':
                        $metadata[$name]['type'] = $schema->type;
                        if ($pattern = $schema->pattern) {
                            $metadata[$name]['type'] = 'regex';
                            $metadata[$name]['pattern'] = $pattern;
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

    public function scan(?LoggerInterface $logger = null): Result
    {
        $builder = (new Builder())
            ->addSource($this->sources)
            ->setMode(Mode::SPEC);

        if ($logger instanceof LoggerInterface) {
            $builder->setLogger($logger);
        }

        $result = $builder->build();

        if ($logger instanceof LoggerInterface) {
            foreach ($result->errors() as $error) {
                $logger->error($error);
            }
            foreach ($result->warnings() as $warning) {
                $logger->warning($warning);
            }
        }

        return $result;
    }
}
