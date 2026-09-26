<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

use OpenApi\Spec as OA;
use Radebatz\OpenApi\Routing\Attributes\Middleware;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\FooMiddleware;

/**
 * Carries a `PathItem` but no operations, so everything on it reaches routes via a subclass.
 */
#[OA\PathItem(prefix: '/base')]
#[OA\Response(response: 401, description: 'Unauthorized')]
#[Middleware(names: [FooMiddleware::class])]
abstract class BaseApiController
{
}
