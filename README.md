# openapi-router

[![Build Status](https://travis-ci.org/DerManoMann/openapi-router.png)](https://travis-ci.org/DerManoMann/openapi-router)

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
