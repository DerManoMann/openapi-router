<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware;

use Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers\FakeResponse;

class BarMiddleware
{
    public function __invoke($request, $handlerOrResponse = null, $next = null)
    {
        return FakeResponse::create('invoke');
    }
}
