# Upgrading to 5.x

5.x reads attributes through swagger-php's **spec pipeline** (`OpenApi\Spec`) instead of the
classic annotation pipeline, and drops `radebatz/openapi-extras`. Every controller attribute
changes, and so does the router's configuration API.

The reason it is worth doing: `OA\PathItem` — core swagger-php — does everything
openapi-extras' `Controller` did and several things it could not.

## Requirements

| | 4.x | 5.x |
|---|---|---|
| PHP | 8.1+ | 8.2+ |
| swagger-php | `^4.11 \|\| ^5.0 \|\| ^6.0` | `^6.9` |
| `radebatz/openapi-extras` | required | removed |
| docblock annotations | supported | not read at all |

## Controller attributes

### `OAX\Controller` becomes `OA\PathItem`

```diff
-use OpenApi\Attributes as OA;
-use Radebatz\OpenApi\Extras\Attributes as OAX;
+use OpenApi\Spec as OA;

-#[OAX\Controller(prefix: '/api/v1', tags: ['pets'])]
+#[OA\PathItem(prefix: '/api/v1', tags: ['pets'])]
 #[OA\Response(response: 401, description: 'Unauthorized')]
 class PetController
```

`prefix`, `tags` and `responses` behave as before. `PathItem` adds what `Controller` had no
way to express:

- **`parameters`** — shared path parameters, emitted at path level as the specification
  defines them instead of repeated on every operation
- **`servers`**, **`summary`**, **`description`** — also real path-level output
- **`security`** — cloned to contained operations the way tags and responses are
- **`ref`** — the path item can itself be a `$ref`

Composition also improved: prefixes compose along the whole class hierarchy, and tags,
security, responses and parameters accumulate from every ancestor with deduplication by
value, scheme, status code and name+in respectively.

**Two `Controller` features have no equivalent.** `middlewares` moves to this package's own
attribute (below). **`inherit: false`**, which stopped the ancestor walk at a given class,
is gone — `PathItem` composition is unconditional. If you relied on it, flatten the
hierarchy or stop inheriting from the ancestor carrying the unwanted metadata.

### Operation attributes are namespaced

```diff
-#[OA\Get(path: '/pets/{id}')]
+#[OA\Operation\Get(path: '/pets/{id}')]
```

Likewise `Post`, `Put`, `Patch`, `Delete`, `Head`, `Options`, `Trace`. See swagger-php's
[migration notes](https://zircote.github.io/swagger-php/) for the full attribute mapping —
this package only changes the two things it owns.

### `OAX\Middleware` becomes `Middleware`

```diff
-use Radebatz\OpenApi\Extras\Attributes as OAX;
+use Radebatz\OpenApi\Routing\Attributes\Middleware;

-#[OAX\Middleware(names: ['auth'])]
+#[Middleware(names: ['auth'])]
```

Same shape, and it now lives here rather than in a separate package. A `#[Middleware]` with
no `OA\Operation` or `OA\PathItem` beside it is now an error instead of being silently
ignored.

## What openapi-extras took with it

4.x bundled openapi-extras' processors, so its attributes worked in your controllers whether
or not you depended on the package directly. They are gone, and **re-requiring
`radebatz/openapi-extras` will not bring them back** — `JsonResponse` extends
`OpenApi\Annotations\Response`, so these are classic-pipeline annotations that the spec
pipeline cannot see.

### `JsonRequestBody` / `JsonResponse`

Shorthand for JSON content. Write it out:

```diff
-#[OAX\JsonResponse(response: 200, ref: Pet::class)]
+#[OA\Response(response: 200, description: 'A pet', content: [
+    new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(ref: Pet::class)),
+])]
```

### `JsonResponse`'s `wrap`

`wrap: 'data'` nested the payload under an envelope key. There is no equivalent — it was
bespoke. Either declare the wrapper schema explicitly, or reimplement it as a swagger-php
augmenter and register it through [`withBuilder()`](Configuration.md).

### `EnumDescription` and `Customizers`

swagger-php ships `Augmenter\EnumDescriptions`, which covers the first. `Customizers` and
`addCustomizer()` are replaced by the builder hooks reachable through `withBuilder()`.

## Configuration

The options array is gone; each option is a fluent setter.

```diff
-$options = [
-    OpenApiRouter::OPTION_RELOAD => false,
-    OpenApiRouter::OPTION_CACHE => $cache,
-];
-(new OpenApiRouter($sources, new SlimRoutingAdapter($app), $options))
-    ->registerRoutes();
+(new OpenApiRouter($sources, new SlimRoutingAdapter($app)))
+    ->setReload(false)
+    ->setCache($cache)
+    ->registerRoutes();
```

| 4.x | 5.x |
|---|---|
| `OPTION_RELOAD` | `setReload()` |
| `OPTION_CACHE` | `setCache()` |
| `OPTION_OA_OPERATION_ID_AS_NAME` | `setOperationIdAsName()` |
| `OPTION_OA_INFO_INJECT` | removed — declare `OA\Info` yourself, or let document generation handle it |
| `OPTION_AUTO_REGEX` (adapter options array) | `$autoRegex` constructor argument |
| — | `withBuilder()` is new: a callable configuring the swagger-php builder |
| `RoutingAdapterInterface::OPTION_NAMESPACE` | removed with the Lumen adapter in 4.x |

`CACHE_KEY_OPENAPI` is now `CACHE_KEY_ROUTES`: the extracted routes are cached rather than
the specification, because a `Spec\Operation` holds a live `\Reflector` and cannot be
serialized. Clear any existing cache on upgrade.

## `scan()` is removed

4.x exposed `scan()` to produce the OpenAPI document. It no longer contributes anything —
the output is byte-identical to running swagger-php over the same sources — so generate the
document with swagger-php directly:

```sh
./vendor/bin/openapi --mode spec -o openapi.yaml src/Controllers
```

In 4.x this was not possible: the document was wrong without openapi-extras' processors,
which is why `scan()` existed.

## Custom adapters

`RoutingAdapterInterface::register()` takes a single `RouteRegistration` instead of four
arguments:

```diff
-public function register(Operation $operation, string $controller, array $parameters, array $custom): void
+public function register(RouteRegistration $route): void
```

The values are the same, as properties: `$route->path`, `->method`, `->controller`,
`->parameters`, `->custom`. Adapters no longer depend on swagger-php at all.
