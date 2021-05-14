# openapi-router

[![Build Status](https://github.com/DerManoMann/openapi-router/workflows/build/badge.svg)](https://github.com/DerManoMann/openapi-router/actions?query=workflow:build)
[![Coverage Status](https://coveralls.io/repos/github/DerManoMann/openapi-router/badge.svg)](https://coveralls.io/github/DerManoMann/openapi-router)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Introduction
Allows to (re-)use [Swagger-PHP](https://github.com/zircote/swagger-php) annotations to configure routes in the
following frameworks:
* [Laravel](https://github.com/laravel/laravel)
* [Lumen](https://github.com/laravel/lumen)
* [Slim](https://github.com/slimphp/Slim)
* [Silex](https://github.com/silexphp/Silex) (Deprecated)


## Requirements
* [PHP 7.2 or higher](http://www.php.net/)

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

**NOTE:** If you are using the included [extended OpenApi Annotations](src/Annotations) without composer autoloading
you most likely need to run this line of code before generating OpenAPI documentation (swagger.json, etc.):
```php
\Radebatz\OpenApi\Routing\OpenApiRouter::register();
```

## Basic usage

Example using the `Slim` framework adapter and standard [OpenApi annotations](https://github.com/zircote/swagger-php/tree/master/src/Annotations) only.

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

**Controller**
```php
<?php

namespace MyApp\Controllers;

class GetController
{

    /**
     * @OA\Get(
     *     path="/getme",
     *     x={
     *       "name": "getme"
     *     },
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function getme($request, $response) {
        return $response->write('Get me');
    }
}
```

## Documentation
* [Configuration](docs/Configuration.md)
* [Annotation extensions](docs/AnnotationExtensions.md)

## License

The openapi-router project is released under the [MIT license](LICENSE).
