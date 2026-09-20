<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Routing;

use OpenApi\Spec as OA;

/**
 * Path parameters typed as an OpenAPI 3.1 type list.
 *
 * Outside `Fixtures/Controllers` so the framework tests keep a fixed route table.
 */
class UnionTypeController
{
    #[OA\Operation\Get(path: '/nullable/{id}', x: ['name' => 'nullable'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function nullable(
        #[OA\Parameter\Path(name: 'id', schema: new OA\Schema(type: ['integer', 'null']))]
        $id = null
    ) {
    }

    #[OA\Operation\Get(path: '/union/{id}', x: ['name' => 'union'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function union(
        #[OA\Parameter\Path(name: 'id', schema: new OA\Schema(type: ['integer', 'string']))]
        $id = null
    ) {
    }
}
