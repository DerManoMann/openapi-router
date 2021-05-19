<?php

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures;

class FooMiddleware
{
    public function __invoke($request, $response, $next)
    {
        return $next($request, $response);
    }
}
