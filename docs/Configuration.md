# Configuration

## Global Configuration
The `OpenApiRouter` class takes an array (map) as optional third constructor argument which allows to customise
its behaviour.

All option names (keys) are defined as class constants.

**`OPTION_RELOAD`**
---
Enforces loading of route annotations on each request.

Typically you want this turned off on production. Requires a cache confgured (annotation caching) or caching support implemented by the used adapter. 

Default: `true`

**`OPTION_CACHE`**
---
Instance of a PSR-16 simple cache.

Used for caching of parsed OpenApi annotations if the `reload` option is disabled.

Default: `null`

**`OPTION_OA_INFO_INJECT`**
---
Controls whether to inject a default `@OA\Info` instance while scanning.

This can be useful if your top level OpenApi annotation is inside the scanned folder hierarchy.

Default: `true`

**`OPTION_OA_OPERATION_ID_AS_NAME`**
---
Controls whether to default the custom (x-) name property to the `operationId`.

Allows to set the route name via the standard `operationId` rather than `x-name`.
By default the `operationId` is populated with the controller (class/method) for the route.  

Default: `true`

### Example use
```php

use Radebatz\OpenApi\Routing\Adapters\LaravelRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Symfony\Component\Cache\Simple\ArrayCache;

    $options = [
        OpenApiRouter::OPTION_RELOAD => true,
        OpenApiRouter::OPTION_CACHE => new ArrayCache(),
    ];
    
    (new OpenApiRouter([__DIR__ . '/Fixtures/Laravel'], new LaravelRoutingAdapter($app), $options))
        ->registerRoutes();
```

## Adapter Configuration
Each framework is different and that means that not all features are available in all adapters.
Configuration keys are available as constants on the `RoutingAdapterInterface` interface.

Right now these options are available:

**`OPTION_AUTO_REGEX`**
---
When enabled the adapter will automatically configure a `[0-9]+` regex for any path elements defined as integer. 

Available for:
* All adapters

   default: `true`

**`OPTION_NAMESPACE`**
---
Specifies a base namespace for all controllers. If set this will be removed from the controller classes passed into the
framework router.

Available for:
* `LaravelRouteringAdapter`

   default: `'App\\Http\\Controllers\\'`
* `LumneRoutingAdapter`
 
   default: `'App\\Http\\Controllers\\'`

### Example use

```php

use Radebatz\OpenApi\Routing\Adapters\LaravelRoutingAdapter;
use Radebatz\OpenApi\Routing\OpenApiRouter;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;
use Symfony\Component\Cache\Simple\ArrayCache;

    $options = [
        OpenApiRouter::OPTION_RELOAD => true,
        OpenApiRouter::OPTION_CACHE => new ArrayCache(),
    ];
    
    $adapterOptions = [
        RoutingAdapterInterface::OPTION_AUTO_REGEX => false,
        RoutingAdapterInterface::OPTION_NAMESPACE => 'My\\App',
    ];
    
    (new OpenApiRouter([__DIR__ . '/Fixtures/Laravel'], new LaravelRoutingAdapter($app, $adapterOptions), $options))
        ->registerRoutes();
```
