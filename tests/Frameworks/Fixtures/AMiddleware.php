<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures;

use Closure;

class AMiddleware
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
