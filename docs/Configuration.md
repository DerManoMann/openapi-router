# Configuration

## Global Configuration
The `OpenApiRouter` class takes an array (map) as optional third constructor argument which allows to customise
its behaviour.

All option names (keys) are defined as class constants in `Radebatz\OpenApi\Routing\OpenApiRouter`.

**`OPTION_RELOAD`**
---
Enforces loading of route annotations on each request.

Typically you want this turned off on production. Requires a cache confgured (annotation caching) or caching support implemented by the used adapter. 

**Note**: When using a framework it is recommended to rely on the framework caching rather than using the (simple) build in cache. 

Default: `true`

**`OPTION_CACHE`**
---
Instance of a PSR-16 simple cache.

Used for caching of parsed OpenApi annotations if the `reload` option is disabled.

Default: `null`

**`OPTION_OA_INFO_INJECT`**
---
Controls whether to inject a default `@OA\Info` instance while scanning.

This can be useful for testing or small projects.

Default: `false`

**`OPTION_OA_OPERATION_ID_AS_NAME`**
---
Controls whether to use the configured `operationId` as the route name. If disabled the adapter will look for a vendor property
`x-name` on the operation (`Get`, `Post`, etc.) attribute.

Allows to set the route name via the standard `operationId` rather than the vendor `x-name`.

**Note**: The default for `operationId` in `swagger-php` is to generate an operationId and hash it if it is explicitely set. generated 

Default: `true`

### Example use
```php
<?php

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
Adapter configuration keys are available as constants on the `RoutingAdapterInterface` interface.

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
<?php

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
