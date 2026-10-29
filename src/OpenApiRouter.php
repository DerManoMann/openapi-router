<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing;

use OpenApi\Builder;
use OpenApi\Builder\Mode;
use OpenApi\Spec as OA;
use OpenApi\Specification;
use OpenApi\Specification\ComponentIndex;
use OpenApi\Specification\PathItemHierarchy;
use OpenApi\Undefined;
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
        $hierarchy = $specification->buildPathItemHierarchy();
        $index = $specification->buildComponentIndex();

        $routes = [];
        foreach ($specification->operations as $operation) {
            $reflector = $operation->getReflector();
            if (!$reflector instanceof \ReflectionMethod) {
                // no declaring method to dispatch to (e.g. a plain function) — nothing to register
                continue;
            }

            $controller = $operation->getClassName() . '::' . $reflector->getName();
            $pathItems = $hierarchy->forOperation($operation);

            $routes[] = new RouteRegistration(
                path: $operation->path ?? '',
                method: strtoupper($operation->method ?? 'GET'),
                controller: $controller,
                parameters: $this->parameterMetadata(array_reverse($this->pathParameters($operation, $hierarchy, $index)), $index),
                custom: $this->customProperties($operation, $pathItems),
            );
        }

        return $routes;
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
     * A parameter that is only a `ref` is returned as the component it points to, since that
     * is where its schema and `required` are.
     *
     * @return list<OA\Parameter>
     */
    protected function pathParameters(OA\Operation $operation, PathItemHierarchy $hierarchy, ComponentIndex $index): array
    {
        $parameters = $hierarchy->parametersFor($operation);
        foreach ($operation->parameters ?? [] as $parameter) {
            if (($key = $index->parameterKey($parameter)) !== null) {
                $parameters[$key] = $parameter;
            }
        }

        $pathParameters = [];
        foreach ($parameters as $parameter) {
            $parameter = (is_string($parameter->ref) ? $index->findParameter($parameter->ref) : null) ?? $parameter;
            if ('path' === $parameter->in && $parameter->name !== null) {
                $pathParameters[] = $parameter;
            }
        }

        return $pathParameters;
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
     * A schema that is only a `ref` is read from the component it points to, as a backed enum
     * parameter usually is.
     *
     * @param list<OA\Parameter> $parameters
     *
     * @return array<string,array{required: bool, type: ?string, pattern: ?string, default: int|float|string|null}>
     */
    protected function parameterMetadata(array $parameters, ComponentIndex $index): array
    {
        $metadata = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->name;

            $metadata[$name] = [
                'required' => (bool) $parameter->required,
                'type' => null,
                'pattern' => null,
                'default' => null,
            ];

            if ($schema = $parameter->schema) {
                $schema = (is_string($schema->ref) ? $index->findSchema($schema->ref) : null) ?? $schema;

                $type = $this->routableType($schema->type);
                if (in_array($type, ['string', 'integer'], true)) {
                    $metadata[$name]['type'] = $type;
                }

                if (($pattern = $this->constraint($schema)) !== null) {
                    $metadata[$name]['type'] = 'regex';
                    $metadata[$name]['pattern'] = $pattern;
                }

                $metadata[$name]['default'] = $this->routeValue($schema->default);
            }
        }

        return $metadata;
    }

    /**
     * The regex a schema constrains a path parameter to.
     *
     * An explicit `pattern` wins, then `enum`, then a `format` with a known shape. The `uuid`
     * and `ulid` expressions are the ones Laravel's `whereUuid()` and `whereUlid()` use.
     */
    protected function constraint(OA\Schema $schema): ?string
    {
        if ($schema->pattern) {
            return $schema->pattern;
        }

        $values = array_filter(
            array_map($this->routeValue(...), $schema->enum ?? []),
            static fn (int|float|string|null $value): bool => $value !== null,
        );
        if ($values !== []) {
            return implode('|', array_map(static fn (int|float|string $value): string => preg_quote((string) $value), $values));
        }

        return match ($schema->format) {
            'uuid' => '[\da-fA-F]{8}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{12}',
            'ulid' => '[0-7][0-9a-hjkmnp-tv-zA-HJKMNP-TV-Z]{25}',
            default => null,
        };
    }

    /**
     * A schema value as it appears in a URL: a scalar, or an enum case's value or name.
     *
     * `null`, booleans, arrays and an undefined value have no place in a path.
     */
    protected function routeValue(mixed $value): int|float|string|null
    {
        return match (true) {
            $value instanceof \BackedEnum => $value->value,
            $value instanceof \UnitEnum => $value->name,
            Undefined::isDefault($value) => null,
            is_int($value), is_float($value), is_string($value) => $value,
            default => null,
        };
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
