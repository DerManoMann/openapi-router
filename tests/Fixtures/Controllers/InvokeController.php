<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

use OpenApi\Spec as OA;

#[OA\PathItem(prefix: '/foo')]
#[OA\Response(response: 401, description: 'Unauthorized')]
class InvokeController
{
    #[OA\Operation\Get(path: '/invoke/{name}', x: ['name' => 'invoke'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function __invoke($name)
    {
        return FakeResponse::create('invoke');
    }
}
