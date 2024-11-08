# openapi-router

[![Build Status](https://github.com/DerManoMann/openapi-router/workflows/build/badge.svg)](https://github.com/DerManoMann/openapi-router/actions)
[![Coverage Status](https://coveralls.io/repos/github/DerManoMann/openapi-router/badge.svg)](https://coveralls.io/github/DerManoMann/openapi-router)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Introduction
Allows to (re-)use [Swagger-PHP](https://github.com/zircote/swagger-php) attributes (docblock annotations are deprecated),
to configure routes in the following frameworks:

* [Laravel](https://github.com/laravel/laravel)
* [Lumen](https://github.com/laravel/lumen)
* [Slim](https://github.com/slimphp/Slim)


## Requirements
* [PHP 8.1 or higher](http://www.php.net/) - depending on framework version.

## Installation

You can use **composer** or simply **download the release**.

**Composer**

The preferred method is via [composer](https://getcomposer.org). Follow the
[installation instructions](https://getcomposer.org/doc/00-intro.md) if you do not already have
composer installed.

Once composer is installed, execute the following command in your project root to install this library:

```sh
composer require radebatz/openapi-router
```
After that all required classes should be availabe in your project to add routing support.

## Basic usage

Example using the `Slim` framework adapter and standard [OpenApi attributes](https://zircote.github.io/swagger-php/guide/attributes) only.

**Controller**
```php
<?php

namespace MyApp\Controllers\V1;

use OpenApi\Attributes as OA;
use Radebatz\OpenApi\Extras\Attributes as OAX;

/* Things shared by all endpoints in this controller.*/
#[OAX\Controller(prefix: '/api/v1')]
#[OA\Response(response: 200, description: 'OK')]
#[OAX\Middleware(names: ['auth', 'admin'])]
class GetController
{
    #[OA\Get(path: '/getme', operationId: 'getme')]
    #[OA\Response(response: 400, description: 'Not good enough')]
    public function getme($request, $response) {
        return $response->write('Get me');
    }
}
```

**index.php**
```php
<?php

use Radebatz\OpenApi\Routing\Adapters\SlimRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Slim\App;

require '../vendor/autoload.php';

$app = new App();
(new OpenApiRouter([__DIR__ . '/../src/controllers'], new SlimRoutingAdapter($app)))
    ->registerRoutes();

$app->run();
```

## Documentation
* [Configuration](docs/Configuration.md)

## License

The openapi-router project is released under the [MIT license](LICENSE).
