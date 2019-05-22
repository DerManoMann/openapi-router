# openapi-router

[![Build Status](https://travis-ci.org/DerManoMann/openapi-router.png)](https://travis-ci.org/DerManoMann/openapi-router)

## Introduction ##
Allows to re-use [Swagger-PHP](https://github.com/zircote/swagger-php) annotations for configuring routes in the following frameworks:
* [Laravel](https://github.com/laravel/laravel)
* [Lumen](https://github.com/laravel/lumen)
* [Slim](https://github.com/slimphp/Slim)
* [Silex](https://github.com/silexphp/Silex) (Deprecated)


## Requirements ##
* [PHP 7.1 or higher](http://www.php.net/)

## Installation ##

You can use **Composer** or simply **Download the Release**

### Composer ###

The preferred method is via [composer](https://getcomposer.org). Follow the
[installation instructions](https://getcomposer.org/doc/00-intro.md) if you do not already have
composer installed.

Once composer is installed, execute the following command in your project root to install this library:

```sh
composer require radebatz/openapi-router
```

# Configuration ##
The `OpenApiRouter` class takes an array (map) as optional third constructor argument which allows to customise the behaviour.
All option names (keys) are defined as class constants.

**`OPTION_REVALIDATE`**
---
Enforces reparsing of route annotations on each load.

Typically you want this turned off on production. Requires a cache confgured (annotation caching) or caching support implemented by the used adapter. 

Default: `true`

**`OPTION_CACHE`**
---
Instance of a PSR-16 simple cache.

Used for caching of parsed OpenApi annotations if the `revalidate` option is disabled.

Default: `null`


## Usage ##

Example usage using `Slim`.

### index.php ###
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

### Controller ###
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
