<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing;

use OpenApi\Builder;
use OpenApi\Builder\Mode;
use OpenApi\Spec as OA;
use OpenApi\Specification;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Radebatz\OpenApi\Routing\Attributes\Middleware;

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

    /** @var (callable(Builder): (Builder|void))|null */
    protected $builderHook;

    /**
     * @param string|\SplFileInfo|\Reflector|iterable<mixed> $sources directories, filenames or reflectors to scan — whatever `Builder::addSource()` accepts
     */
    public function __construct(
        protected string|\SplFileInfo|\Reflector|iterable $sources,
        protected RoutingAdapterInterface $routingAdapter,
    ) {
    }

    /**
     * Configure the builder before it runs.
     *
     * The hook receives a builder already carrying this package's sources, spec mode and
     * logger, and may modify it in place or return a replacement — the same shape as
     * swagger-php's own `withResolver()` and `withAugmenters()`.
     *
     * Relevant to routing when it changes which operations are discovered; configuration
     * that only shapes the document belongs in a direct swagger-php call.
     *
     * @param callable(Builder): (Builder|void) $hook
     */
    public function withBuilder(callable $hook): static
    {
        $this->builderHook = $hook;

        return $this;
    }

    /**
     * The builder for this scan, hook applied.
     */
    protected function builder(): Builder
    {
        $builder = (new Builder())
            ->addSource($this->sources)
            ->setMode(Mode::SPEC);

        if ($this->logger instanceof LoggerInterface) {
            $builder->setLogger($this->logger);
        }

        if ($this->builderHook !== null) {
            $customised = ($this->builderHook)($builder);
            if ($customised instanceof Builder) {
                $builder = $customised;
            }
        }

        return $builder;
    }

    /**
     * Rescan on every {@see registerRoutes()} call.
     *
     * Bypasses both the configured cache and the adapter's own cached routes. Typically off
     * in production.
     */
    public function setReload(bool $reload = true): static
    {
        $this->reload = $reload;

        return $this;
    }

    /**
     * Cache extracted routes here across requests when {@see setReload()} is off.
     */
    public function setCache(?CacheInterface $cache): static
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Use the operation's `operationId` as the route name.
     *
     * When off, only an explicit `x-name` vendor property names a route.
     */
    public function setOperationIdAsName(bool $operationIdAsName = true): static
    {
        $this->operationIdAsName = $operationIdAsName;

        return $this;
    }

    /**
     * PSR-3 logger for the scan.
     */
    public function setLogger(?LoggerInterface $logger): static
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

        $cache = $this->reload ? null : $this->cache;

        /** @var list<RouteRegistration>|null $routes */
        $routes = is_array($cached = $cache?->get(self::CACHE_KEY_ROUTES)) ? array_values($cached) : null;

        if ($routes === null) {
            $routes = $this->extractRoutes(
                $this->builder()->build()->specification() ?? new Specification()
            );

            $cache?->set(self::CACHE_KEY_ROUTES, $routes);
        }

        foreach ($routes as $route) {
            $this->routingAdapter->register($route);
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
            $pathItems = $this->governingPathItems($operation->getClassName(), $classToPathItem);

            $routes[] = new RouteRegistration(
                path: $operation->path ?? '',
                method: strtoupper($operation->method ?? 'GET'),
                controller: $controller,
                parameters: $this->parameterMetadata(array_reverse($this->pathParameters($operation, $pathItems))),
                custom: $this->customProperties($operation, $pathItems),
            );
        }

        return $routes;
    }

    /**
     * The `PathItem` chain governing an operation, outermost ancestor first.
     *
     * swagger-php resolves a class without its own `PathItem` against its ancestors — which
     * is how a base controller's prefix reaches a subclass's operations — so anything read
     * off a `PathItem` here has to walk the same hierarchy or it silently applies to nothing.
     *
     * @param array<string, OA\PathItem> $classToPathItem
     *
     * @return list<OA\PathItem>
     */
    protected function governingPathItems(?string $className, array $classToPathItem): array
    {
        if ($className === null || !class_exists($className)) {
            return [];
        }

        $pathItems = [];
        for ($current = new \ReflectionClass($className); $current !== false; $current = $current->getParentClass()) {
            if (isset($classToPathItem[$current->getName()])) {
                $pathItems[] = $classToPathItem[$current->getName()];
            }
        }

        return array_reverse($pathItems);
    }

    /**
     * The path parameters applying to an operation, in path order.
     *
     * `PathItem::$parameters` are shared by every operation under it and are emitted at path
     * level, so swagger-php never copies them onto the operations themselves — but routing
     * still needs them, or a placeholder declared once for the whole controller ends up with
     * no type, pattern or optionality. A parameter the operation declares itself wins, while
     * keeping the position the path item gave it.
     *
     * @param list<OA\PathItem> $pathItems
     *
     * @return list<OA\Parameter>
     */
    protected function pathParameters(OA\Operation $operation, array $pathItems): array
    {
        $parameters = [];

        foreach ([...$pathItems, $operation] as $holder) {
            foreach ($holder->parameters ?? [] as $parameter) {
                if ('path' === $parameter->in && $parameter->name !== null) {
                    $parameters[$parameter->name] = $parameter;
                }
            }
        }

        return array_values($parameters);
    }

    /**
     * @param list<OA\PathItem> $pathItems
     *
     * @return array<string,mixed>
     */
    protected function customProperties(OA\Operation $operation, array $pathItems): array
    {
        // PathItem's own class-level attachables don't clone down to its operations the way
        // tags/security/responses do (swagger-php's `PathItems` augmenter only clones those
        // three) — resolved here instead, so a controller-level `#[Middleware]` still applies
        // to every operation under it, inherited ones included.
        $attachables = [];
        foreach ([...$pathItems, $operation] as $holder) {
            $attachables = [...$attachables, ...$holder->attachables ?? []];
        }

        $middleware = [];
        foreach ($attachables as $attachable) {
            if ($attachable instanceof Middleware) {
                $middleware = [...$middleware, ...$attachable->names];
            }
        }

        $name = $this->operationIdAsName ? $operation->operationId : null;

        $x = $operation->x ?? [];
        if (array_key_exists(RoutingAdapterInterface::X_NAME, $x)) {
            $name = $x[RoutingAdapterInterface::X_NAME];
        }
        if (array_key_exists(RoutingAdapterInterface::X_MIDDLEWARE, $x)) {
            $middleware = [...$middleware, ...array_values((array) $x[RoutingAdapterInterface::X_MIDDLEWARE])];
        }

        return [
            RoutingAdapterInterface::X_NAME => $name,
            RoutingAdapterInterface::X_MIDDLEWARE => array_values(array_unique($middleware)),
        ];
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
