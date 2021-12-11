<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures;

use Closure;

class AMiddleware
{
    public function __invoke($request, $next)
    {
        return $next->handle($request);
    }

    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
