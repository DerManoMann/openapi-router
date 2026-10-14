<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Routing;

use OpenApi\Spec as OA;

/**
 * Declares its path parameter on the `PathItem` as a reference to a component.
 *
 * Outside `Fixtures/Controllers` so the framework tests keep a fixed route table.
 */
#[OA\PathItem(prefix: '/ref', parameters: [
    new OA\Parameter(ref: '#/components/parameters/tenant'),
])]
class RefParameterController
{
    #[OA\Operation\Get(path: '/{tenant}/items', x: ['name' => 'ref'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function items($tenant = null)
    {
    }
}
