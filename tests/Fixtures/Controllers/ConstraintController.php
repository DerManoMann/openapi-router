<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

use OpenApi\Spec as OA;

class ConstraintController
{
    #[OA\Operation\Get(path: '/lang/{lang}', x: ['name' => 'lang'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function lang(
        #[OA\Parameter\Path(description: 'The language', schema: new OA\Schema(type: 'string', enum: ['en', 'de']))]
        $lang
    ) {
        return FakeResponse::create(sprintf('Lang: %s', $lang));
    }

    #[OA\Operation\Get(path: '/item/{id}', x: ['name' => 'item'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function item(
        #[OA\Parameter\Path(description: 'The id', schema: new OA\Schema(type: 'string', format: 'uuid'))]
        $id
    ) {
        return FakeResponse::create(sprintf('Item: %s', $id));
    }

    #[OA\Operation\Get(path: '/list/{page}', x: ['name' => 'list'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function list(
        #[OA\Parameter\Path(description: 'The page', required: false, schema: new OA\Schema(type: 'integer', default: 1))]
        $page
    ) {
        return FakeResponse::create(sprintf('Page: %s', $page));
    }
}
