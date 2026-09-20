# openapi-router

Configures a PHP framework's router from the swagger-php **spec attributes**
describing an API, so the routing table and the OpenAPI document cannot drift apart.

Terminology for this package. swagger-php's own vocabulary is defined in
[its CONTEXT.md](https://github.com/zircote/swagger-php/blob/master/CONTEXT.md) and is not
repeated here — where a word belongs to both, theirs wins.

## Language

### Core concepts

**Route**:
One framework-level mapping of method + path to a handler. What this package produces.
_Avoid_: endpoint (an API-design word, not a framework one), path (means the URL alone)

**Operation**:
A swagger-php `Spec\Operation` — one HTTP method on one path, as declared in an attribute.
The **input** side of what this package does; a route is the output.
_Avoid_: using it for the registered route, or as a synonym for **action**

**Adapter**:
The per-framework translator that registers a route. One implementation per framework,
behind `RoutingAdapterInterface`.
_Avoid_: driver, bridge, integration

**Registration**:
The act of handing one route to the framework's router, and the `RouteRegistration` value
object carrying everything needed to do it.
_Avoid_: binding, mapping (both mean something else in Laravel)

**Controller**:
The `Class::method` string a route dispatches to. Written as a **handler** by some
frameworks.
_Avoid_: action, callable — `RouteRegistration::$controller` is the name, and it is always
`Class::method`, never a closure

### Attributes

**Middleware**:
This package's own attribute, and the only one it defines. An `Attachable`, so it is routing
metadata that never reaches the OpenAPI document.
_Avoid_: calling it an OpenAPI attribute — it is not one, which is why it lives outside the
`OpenApi\` namespace

**Vendor property**:
A vendor extension on an operation that this package reads. Declared unprefixed —
`x: ['name' => ...]` — and emitted with the prefix, as `x-name`. Say which form you mean.
_Avoid_: naming the declared key `x-name`; that is the emitted spelling, and the constants
(`X_NAME`, `X_MIDDLEWARE`) hold the unprefixed one

**Attachable**:
swagger-php's extension point for metadata that rides along with an attribute without
appearing in the output. Theirs, not ours — see their CONTEXT.md.

### Configuration

**`set*`**:
A fluent setter taking a **value**. Follows swagger-php, where the prefix means exactly this.
_Avoid_: `with*` for a value — the prefix is load-bearing

**`with*`**:
A fluent setter taking a **callable** that configures something. `withBuilder()` is the only
one here.
_Avoid_: `with*` taking the configured object itself; swagger-php has no such method and it
reintroduces the question of what the caller must supply

**Source**:
Whatever is scanned for attributes — directories, files or reflectors. Passed to the
constructor and forwarded to `Builder::addSource()`.
_Avoid_: path, directory (both too narrow), input

## Relationships

- An **adapter** receives a **registration** per **route** and translates it for its framework
- A **registration** is derived from one **operation**, plus any **middleware** attached to it
  or to its containing `PathItem`, plus any **vendor properties**
- **Sources** feed swagger-php's `Builder`, which produces the `Specification` the
  **operations** are read from
- `withBuilder()` configures that `Builder`; it matters to routing only when it changes
  *which operations are discovered*

## Example dialogue

> **Dev:** "My middleware isn't being applied."
> **Domain expert:** "Is the `#[Middleware]` beside an **operation** or a `PathItem`? It has
> nothing to merge into anywhere else, so the scan raises an error."

> **Dev:** "Can I get the OpenAPI document out of the router?"
> **Domain expert:** "No — this package contributes nothing to it. Run swagger-php over the
> same **sources**; the output is identical."

## Flagged ambiguities

- "route" vs "operation" — **resolved**: an **operation** is declared (swagger-php's input), a
  **route** is registered (the framework's output). This package turns the first into the
  second. Never use them interchangeably, because the whole package is the mapping between
  them.
- "middleware" — **resolved**: always means the framework's middleware, named by the
  `Middleware` attribute or `x-middleware`. Never swagger-php's pipeline stages, which are
  **processors** (classic) or **augmenters** (spec).
- "name" — **resolved**: the *route* name, which `setOperationIdAsName()` derives from the
  `operationId` and `x-name` overrides. Not the operation id itself, and not a path.
- "v2" — **unresolved**, and worth care. The rewrite is called v2 internally and the branch is
  `v2/spec-pipeline`, but it releases as **5.0** — the last tag is 4.0.0. Say "5.x" in
  anything public.
