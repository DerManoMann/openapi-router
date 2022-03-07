<?php

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures;

class BarMiddleware
{
    public function __invoke($request, $handlerOrResponse = null, $next = null)
    {
        return FakeResponse::create('invoke');
    }
}
