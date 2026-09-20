# openapi-router

[![Build Status](https://github.com/DerManoMann/openapi-router/workflows/build/badge.svg)](https://github.com/DerManoMann/openapi-router/actions)
[![Coverage Status](https://coveralls.io/repos/github/DerManoMann/openapi-router/badge.svg)](https://coveralls.io/github/DerManoMann/openapi-router)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Introduction

Configure framework routes from the [swagger-php](https://github.com/zircote/swagger-php)
attributes describing your API, so the routing table and the OpenAPI document cannot drift
apart.

**Spec attributes only.** Routes are read from the `OpenApi\Spec` namespace — swagger-php's
spec pipeline. The classic `OpenApi\Attributes` namespace and docblock annotations are not
read at all, so a codebase on classic needs converting first; see the
[upgrade guide](docs/UpgradingTo5.md) if you are coming from 4.x. Spec attributes are
[marked beta upstream](https://zircote.github.io/swagger-php/guide/spec-attributes) and their
API may still change.

Supported frameworks:

* [Laravel](https://github.com/laravel/laravel)
* [Slim](https://github.com/slimphp/Slim)

## Requirements

* PHP 8.2 or higher
* `zircote/swagger-php` ^6.9

## Installation

```sh
composer require radebatz/openapi-router
```

## Basic usage

**Controller**

```php
<?php

namespace MyApp\Controllers\V1;

use OpenApi\Spec as OA;
use Radebatz\OpenApi\Routing\Attributes\Middleware;

#[OA\PathItem(prefix: '/api/v1')]
#[OA\Response(response: 200, description: 'OK')]
#[Middleware(names: ['auth', 'admin'])]
class GetController
{
    #[OA\Operation\Get(path: '/getme', operationId: 'getme')]
    #[OA\Response(response: 400, description: 'Not good enough')]
    public function getme($request, $response)
    {
        return $response->write('Get me');
    }
}
```

`PathItem` applies to every operation in the class: `prefix` composes into their paths, and
`tags`, `security` and `responses` are shared with them. It composes along the class
hierarchy, so a base controller can carry what its subclasses have in common.

**index.php**

```php
<?php

use Radebatz\OpenApi\Routing\Adapters\SlimRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Slim\Factory\AppFactory;

require '../vendor/autoload.php';

$app = AppFactory::create();

(new OpenApiRouter([__DIR__ . '/../src/controllers'], new SlimRoutingAdapter($app)))
    ->registerRoutes();

$app->run();
```

## Writing the attributes

Every attribute except `#[Middleware]` is swagger-php's, and documented there:

* [Using spec attributes](https://zircote.github.io/swagger-php/guide/spec-attributes) — how
  to write them
* [Spec attribute reference](https://zircote.github.io/swagger-php/reference/spec-attributes)
  — every attribute and its parameters
* [Modes](https://zircote.github.io/swagger-php/guide/modes) — how the spec pipeline differs
  from classic

### `#[Middleware]`

The one attribute this package defines. Names middleware in whatever form the framework
expects — a class-string, or an alias such as Laravel's `auth`.

```php
#[OA\PathItem(prefix: '/pets')]
#[Middleware(names: ['auth'])]              // every route in the controller
class PetController
{
    #[OA\Operation\Get(path: '/{id}')]
    #[Middleware(names: ['throttle:60,1'])] // this route only
    public function show(string $id) {}
}
```

Both apply, class-level first. It is an `Attachable`, so it never appears in the generated
document — middleware is a routing concern, not an API one. A `#[Middleware]` with no
`OA\Operation` or `OA\PathItem` beside it is an error rather than a silent no-op.

### Vendor extensions

Two keys on an operation's `x` argument feed the router. Write them **without** the `x-`
prefix; swagger-php adds it when emitting.

```php
#[OA\Operation\Get(path: '/pets', operationId: 'listPets', x: [
    'name' => 'pets.index',
    'middleware' => ['throttle:60,1'],
])]
```

| Key | In the document | Effect |
|---|---|---|
| `name` | `x-name` | **Replaces** the route name |
| `middleware` | `x-middleware` | **Appends** to the `#[Middleware]` attributes |

Unlike `#[Middleware]`, these **are** emitted into the document — that is the tradeoff
between them. Prefer the attribute unless you want the middleware visible to whatever
consumes your spec.

The constants for the unprefixed keys are `RoutingAdapterInterface::X_NAME` and
`X_MIDDLEWARE`.

## Generating the OpenAPI document

Use swagger-php directly — its CLI or `Builder` over the same sources:

```sh
./vendor/bin/openapi --mode spec -o openapi.yaml src/controllers
```

This package contributes nothing to the document, so routing it through here would only add
a layer.

## Documentation

* [Configuration](docs/Configuration.md)
* [Upgrading to 5.x](docs/UpgradingTo5.md)
* [Terminology](CONTEXT.md) — the words this package uses, and the ones it avoids

## License

The openapi-router project is released under the [MIT license](LICENSE).
