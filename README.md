# openapi-router

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
new OpenApiRouter([__DIR__ . '/../src/controllers'], new SlimRoutingAdapter($app));

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