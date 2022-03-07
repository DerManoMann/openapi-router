<?php

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures;

class FooMiddleware
{
    public function __invoke($request, $handlerOrResponse = null, $next = null)
    {
        return FakeResponse::create('invoke');
    }
}
