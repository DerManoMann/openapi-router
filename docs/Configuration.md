# Configuration

`OpenApiRouter` is configured with fluent setters, each returning the router so calls chain.
All are optional.

Prefixes follow swagger-php's convention: `set*` takes a value, `with*` takes a callable that
configures something.

```php
use Radebatz\OpenApi\Routing\Adapters\LaravelRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;

(new OpenApiRouter([__DIR__ . '/../src/Controllers'], new LaravelRoutingAdapter($app)))
    ->setReload(false)
    ->setCache($psr16Cache)
    ->registerRoutes();
```

## Router

### `setReload(bool $reload = true)`

Rescan on every `registerRoutes()` call, bypassing both the configured cache and the
adapter's own cached routes. Default `true`; turn it off in production.

### `setCache(?CacheInterface $cache)`

A PSR-16 cache for the extracted routes, used when `setReload(false)` is set. Default
`null`.

It holds the routes, not the specification — a `Spec\Operation` carries a live `\Reflector`
and cannot be serialized.

### `setOperationIdAsName(bool $operationIdAsName = true)`

Use each operation's `operationId` as the route name. Default `true`. When off, only an
explicit `x-name` names a route.

Note swagger-php hashes generated operation ids by default, so relying on this without
declaring `operationId` yourself produces hashed route names. Either declare them, or turn
hashing off through the builder:

```php
$router->withBuilder(function (Builder $builder): void {
    $builder->getAugmenters()->get(OpenApi\Augmenter\OperationIds::class)?->setHash(false);
});
```

### `setLogger(?LoggerInterface $logger)`

A PSR-3 logger for the scan, applied to the builder before the `withBuilder()` hook runs.

### `withBuilder(callable $hook)`

Configure the `OpenApi\Builder` before it runs. The hook receives one already carrying this
package's sources, spec mode and logger, and may modify it in place or return a replacement —
the same shape as swagger-php's own `withResolver()` and `withAugmenters()`.

```php
$router->withBuilder(function (Builder $builder): void {
    $builder->withAttributeFactory(
        fn ($factory) => $factory->withTranslators(
            fn ($translators) => $translators->add(new MyTranslator())
        )
    );
})->registerRoutes();
```

Sources and mode are always applied by the router, so the hook cannot accidentally drop them.

This matters for routing when it changes *which operations are discovered* — a translator
turning a framework-native attribute into a `Spec\Operation` adds routes. Configuration that
only shapes the document belongs in a direct swagger-php call instead; this package does not
generate documents.

## Adapters

Both adapters take the framework application and one option:

```php
new LaravelRoutingAdapter($app, autoRegex: false);
new SlimRoutingAdapter($app, autoRegex: false);
```

### `$autoRegex`

Constrain a path parameter declared `type: 'integer'` to `[0-9]+`. Default `true`.

Laravel expresses this as a `where()` constraint, Slim as a `{id:[0-9]+}` placeholder. A
parameter whose schema carries an explicit `pattern` uses that instead, regardless of this
setting.
