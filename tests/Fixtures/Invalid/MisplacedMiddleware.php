<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Invalid;

use OpenApi\Spec as OA;
use Radebatz\OpenApi\Routing\Attributes\Middleware;

/**
 * Deliberately invalid: `Middleware` has no `Operation` or `PathItem` sibling to merge into.
 *
 * Lives outside `Fixtures/Controllers` on purpose — the scan of that directory must stay
 * valid, and this fixture is meant to make assembly fail.
 */
#[OA\Schema(schema: 'Misplaced')]
#[Middleware(names: ['nope'])]
class MisplacedMiddleware
{
}
