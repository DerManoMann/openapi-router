<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing;

use OpenApi\Builder;
use OpenApi\Builder\Mode;
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
    public const CACHE_KEY_ROUTES = 'openapi-router.routes';

    protected bool $reload = true;

    protected ?CacheInterface $cache = null;

    protected bool $operationIdAsName = true;

    protected ?LoggerInterface $logger = null;

    protected ?Builder $builder = null;

    /**
     * @param string|list<string>|Finder $sources The directory(s) or filename(s)
     */
    public function __construct(
        protected string|array|Finder $sources,
        protected RoutingAdapterInterface $routingAdapter,
    ) {
    }

    /**
     * Scan with a caller-configured builder instead of {@see defaultBuilder()}.
     *
     * The builder is used as given — sources, mode and logger included — so start from
     * `defaultBuilder()` to keep this package's defaults.
     */
    public function withBuilder(?Builder $builder): static
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * The builder used when none is supplied.
     *
     * Public so it can serve as the starting point for a customised one.
     */
    public function defaultBuilder(): Builder
    {
        $builder = (new Builder())
            ->addSource($this->sources)
            ->setMode(Mode::SPEC);

        if ($this->logger instanceof LoggerInterface) {
            $builder->setLogger($this->logger);
        }

        return $builder;
    }

    /**
     * Rescan on every {@see registerRoutes()} call.
     *
     * Bypasses both the configured cache and the adapter's own cached routes. Typically off
     * in production.
     */
    public function withReload(bool $reload = true): static
    {
        $this->reload = $reload;

        return $this;
    }

    /**
     * Cache extracted routes here across requests when {@see withReload()} is off.
     */
    public function withCache(?CacheInterface $cache): static
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Use the operation's `operationId` as the route name.
     *
     * When off, only an explicit `x-name` vendor property names a route.
     */
    public function withOperationIdAsName(bool $operationIdAsName = true): static
    {
        $this->operationIdAsName = $operationIdAsName;

        return $this;
    }

    /**
     * Logger for {@see defaultBuilder()}.
     *
     * Ignored when a builder is supplied via {@see withBuilder()}, which carries its own.
     */
    public function withLogger(?LoggerInterface $logger): static
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return list<RouteRegistration>|null `null` when the adapter's own route cache was used instead
     */
    public function registerRoutes(): ?array
    {
        if (!$this->reload && $this->routingAdapter->registerCached()) {
            return null;
        }

        $routes = null;
        if ($this->cache instanceof CacheInterface && !$this->reload) {
            $routes = $this->cache->get(self::CACHE_KEY_ROUTES);
        }

        $routes ??= $this->extractRoutes(
            ($this->builder ?? $this->defaultBuilder())->build()->specification() ?? new Specification()
        );

        foreach ($routes as $route) {
            $this->routingAdapter->register($route);
        }

        if ($this->cache instanceof CacheInterface && !$this->reload) {
            $this->cache->set(self::CACHE_KEY_ROUTES, $routes);
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
     * @param array<string, OA\PathItem> $classToPathItem
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
            RoutingAdapterInterface::X_NAME => $this->operationIdAsName ? $operation->operationId : null,
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
     * Extract URI parameter metadata.
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
                switch ($this->routableType($schema->type)) {
                    case 'string':
                        $metadata[$name]['type'] = 'string';
                        if ($pattern = $schema->pattern) {
                            $metadata[$name]['type'] = 'regex';
                            $metadata[$name]['pattern'] = $pattern;
                        }
                        break;
                    case 'integer':
                        $metadata[$name]['type'] = 'integer';
                        break;
                }
            }
        }

        return $metadata;
    }

    /**
     * Reduce a schema type to the single scalar type routing cares about.
     *
     * OpenAPI 3.1 allows a list of types, which is how nullability is expressed
     * (`['integer', 'null']`). `null` is not a routable type, so it is dropped; anything
     * still ambiguous after that (a genuine union) has no single constraint to apply.
     *
     * @param string|list<string>|null $type
     */
    protected function routableType(string|array|null $type): ?string
    {
        if (!is_array($type)) {
            return $type;
        }

        $types = array_values(array_filter($type, static fn (string $candidate): bool => $candidate !== 'null'));

        return count($types) === 1 ? $types[0] : null;
    }
}
