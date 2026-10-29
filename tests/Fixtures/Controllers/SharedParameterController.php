<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

use OpenApi\Spec as OA;

/**
 * Declares its path parameter once on the `PathItem`, the way the specification allows.
 */
#[OA\PathItem(prefix: '/shared', parameters: [
    new OA\Parameter\Path(name: 'tenant', description: 'The tenant', schema: new OA\Schema(type: 'integer')),
])]
class SharedParameterController
{
    #[OA\Operation\Get(path: '/{tenant}/items', x: ['name' => 'shared'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function items($tenant = null)
    {
        return FakeResponse::create(sprintf('Tenant: %s', $tenant));
    }
}
