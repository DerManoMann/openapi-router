# Configuration

`OpenApiRouter` is configured with fluent setters, each returning the router so calls chain.
All are optional.

```php
use Radebatz\OpenApi\Routing\Adapters\LaravelRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;

(new OpenApiRouter([__DIR__ . '/../src/Controllers'], new LaravelRoutingAdapter($app)))
    ->withReload(false)
    ->withCache($psr16Cache)
    ->registerRoutes();
```

## Router

### `withReload(bool $reload = true)`

Rescan on every `registerRoutes()` call, bypassing both the configured cache and the
adapter's own cached routes. Default `true`; turn it off in production.

### `withCache(?CacheInterface $cache)`

A PSR-16 cache for the extracted routes, used when `withReload(false)` is set. Default
`null`.

Routes are cached rather than the specification, because a `Spec\Operation` holds a live
`\Reflector` and cannot be serialized.

### `withOperationIdAsName(bool $operationIdAsName = true)`

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

### `withLogger(?LoggerInterface $logger)`

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

## Vendor properties

Two `x-*` properties on an operation feed the router. Neither reaches the OpenAPI document's
routing behaviour — they are read by this package only.

| Property | Effect |
|---|---|
| `x-name` | **Replaces** the route name, whatever `withOperationIdAsName()` would have produced |
| `x-middleware` | **Appends** to the middleware from `#[Middleware]` attributes |

```php
#[OA\Operation\Get(path: '/pets', operationId: 'listPets', x: [
    'name' => 'pets.index',
    'middleware' => ['throttle:60,1'],
])]
```

The constants are `RoutingAdapterInterface::X_NAME` and `X_MIDDLEWARE`.

## Middleware

`#[Middleware(names: [...])]` attaches middleware in whatever form the framework expects —
a class-string, or an alias such as Laravel's `auth`.

Stack it beside an `OA\Operation` for a single route, or beside an `OA\PathItem` for every
route in that controller; both apply, and the class-level entries come first. It is an
`Attachable` and never appears in the generated document.

A `#[Middleware]` with no `OA\Operation` or `OA\PathItem` beside it is an error rather than a
silent no-op.
