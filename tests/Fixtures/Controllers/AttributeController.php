<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

use OpenApi\Spec as OA;
use Radebatz\OpenApi\Routing\Attributes\Middleware;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\BarMiddleware;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\FooMiddleware;

#[OA\PathItem(prefix: '/attributes')]
#[OA\Response(response: 403, description: 'Not allowed')]
#[Middleware(names: [FooMiddleware::class])]
class AttributeController
{
    #[OA\Operation\Get(path: '/prefixed', x: ['name' => 'attributes'])]
    #[OA\Response(response: 200, description: 'All good')]
    #[Middleware(names: [BarMiddleware::class])]
    public function prefixed()
    {
        return FakeResponse::create('Get fooya');
    }
}
