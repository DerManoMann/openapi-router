<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Routing;

use OpenApi\Spec as OA;

/**
 * Path parameters constrained by `enum`, `format` and `default`.
 *
 * Outside `Fixtures/Controllers` so the framework tests keep a fixed route table.
 */
class FacetsController
{
    #[OA\Operation\Get(path: '/enum/{lang}', x: ['name' => 'enum'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function enum(
        #[OA\Parameter\Path(name: 'lang', schema: new OA\Schema(type: 'string', enum: ['en', 'de']))]
        $lang
    ) {
    }

    #[OA\Operation\Get(path: '/backed/{lang}', x: ['name' => 'backed'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function backed(
        #[OA\Parameter\Path(name: 'lang')]
        Lang $lang
    ) {
    }

    #[OA\Operation\Get(path: '/number/{id}', x: ['name' => 'number'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function number(
        #[OA\Parameter\Path(name: 'id', schema: new OA\Schema(type: 'integer', enum: [1, 2]))]
        $id
    ) {
    }

    #[OA\Operation\Get(path: '/pattern/{lang}', x: ['name' => 'pattern'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function pattern(
        #[OA\Parameter\Path(name: 'lang', schema: new OA\Schema(type: 'string', pattern: '[a-z]{2}', enum: ['en', 'de']))]
        $lang
    ) {
    }

    #[OA\Operation\Get(path: '/uuid/{id}', x: ['name' => 'uuid'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function uuid(
        #[OA\Parameter\Path(name: 'id', schema: new OA\Schema(type: 'string', format: 'uuid'))]
        $id
    ) {
    }

    #[OA\Operation\Get(path: '/ulid/{id}', x: ['name' => 'ulid'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function ulid(
        #[OA\Parameter\Path(name: 'id', schema: new OA\Schema(type: 'string', format: 'ulid'))]
        $id
    ) {
    }

    #[OA\Operation\Get(path: '/page/{page}', x: ['name' => 'page'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function page(
        #[OA\Parameter\Path(name: 'page', required: false, schema: new OA\Schema(type: 'integer', default: 1))]
        $page = 1
    ) {
    }
}
