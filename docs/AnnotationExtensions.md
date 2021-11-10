# Annotation Extensions

## Default Annotations
By default the router expects only standard [OpenApi annotations](https://github.com/zircote/swagger-php/tree/master/src/Annotations).

Theses annotations have only limited build in support for advanced routing features. If you feel like
sticking to those the only way to add more options is to use the
[vendor extension](https://swagger.io/specification/#vendorExtensions) system.

Vendor extensions are OpenApi properties starting with `x-`. Annotations supports this via the `x` annotation property.

**Vendor extension example:**
```php
    ...
    
    /**
     * @OA\Get(
     *     path="/login",
     *     x={
     *       "name": "login",
     *       "middleware": {"auth", "verified"}
     *     },
     *     @OA\Response(response="200", description="All good")
     * )
     */
```     

The example showcases the two vendor extensions that the router supports:
* **name**
  
  The name property is used to bind a (unique) name to each route which later can be used to lookup
  the route (for example to generate a url).
  
  name binding is supported by all adapters.   

* **middleware**

  [Middleware](https://www.php-fig.org/psr/psr-15/) is a concept that only some frameworks support. In those cases
  one or more middleware can be attached to a route as shown above.
  
  Middleware binding is supported by all adapters.

As an alternative to using the x-name property it is also possible to use the standard `operationId` property to configure
a route name.

**NOTE:** It is worth noticing that by default this property is set to `[Controller class]::[method name]` by the swagger-php
library. If you do not wish to use `operationId` it is recommended to disable using it as name value for route binding
(see the global `OPTION_OA_OPERATION_ID_AS_NAME` config option)

## Extended Annotations
### Operations
As an alternative to the above syntax the openapi-router project provides its own (extended) versions of annotations for
all operations (`'get', 'post', 'put', 'patch', 'delete', 'options', 'head'`).

These are registered with a namespace alias of `@OSX`.

**Extended annotation example:**
```php
    ...
    
    /**
     * @OAX\Get(
     *     path="/foo",
     *     operationId="foo",
     *     @OA\Response(response="200", description="All good")
     * )
     */
```

### `Controller` Annotation
openapi-router also provides a `Controller` annotation that can be used to:
* apply a path prefix to all controller routes
* configure middlewares shared by all controller routes 
* configure responses shared by all controller routes

This annotation can be used with both standard and extended operation annotations.

**Controller annotation example:**
```php
...

/**
 * @OAX\Controller(
 *     prefix="api/v1",
 *     middleware={"auth"},
 *     @OA\Response(response=401, description="Not Authenticated")
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="user/{id}/delete",
     *     operationId="transfer",
     *     middleware={"role:admin"},
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public static function delete($request, $response, $id)
    {
        // delete user
    }
```

## Attributes
As of PHP 8.1 swagger-php and openapi-router also allow to use PHP attributes instead of docblock annotations.
Names and features are the same with one exception - for middleware there is a new attribute `Middleware` which avoid having to
use the included customized swagger-php operation annotations.

Here is an example taken from the test suite:
```php

use Radebatz\OpenApi\Routing\Annotations as OAX;

    class AttributeController
    {
        #[OA\Get(path: '/prefixed', x: ['name' => 'attributes'])]
        #[OA\Response(response: 200, description: 'All good')]
        #[OAX\Middleware([BMiddleware::class])]
        public function prefixed()
        {
            return FakeResponse::create('Get fooya');
        }
    }
```
