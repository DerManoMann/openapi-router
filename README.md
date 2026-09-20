# openapi-router

[![Build Status](https://github.com/DerManoMann/openapi-router/workflows/build/badge.svg)](https://github.com/DerManoMann/openapi-router/actions)
[![Coverage Status](https://coveralls.io/repos/github/DerManoMann/openapi-router/badge.svg)](https://coveralls.io/github/DerManoMann/openapi-router)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Introduction

Configure framework routes from the [swagger-php](https://github.com/zircote/swagger-php)
attributes already describing your API, so the routing table and the OpenAPI document cannot
drift apart.

Supported frameworks:

* [Laravel](https://github.com/laravel/laravel)
* [Slim](https://github.com/slimphp/Slim)

## Requirements

* PHP 8.2 or higher
* `zircote/swagger-php` ^6.9

Attributes are read through swagger-php's spec pipeline (`OpenApi\Spec`). Docblock
annotations are not supported — see the [upgrade guide](docs/Upgrading.md) if you are
coming from 4.x.

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

`Middleware` is this package's own attribute — middleware is a routing concern and has no
place in the OpenAPI document, so it never appears in the output. Stack it on the class for
every route in it, or on a single method.

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

## Generating the OpenAPI document

Use swagger-php directly — its CLI or `Builder` over the same sources:

```sh
./vendor/bin/openapi --mode spec -o openapi.yaml src/controllers
```

This package contributes nothing to the document, so routing it through here would only add
a layer.

## Documentation

* [Configuration](docs/Configuration.md)
* [Upgrading to 5.x](docs/Upgrading.md)

## License

The openapi-router project is released under the [MIT license](LICENSE).
