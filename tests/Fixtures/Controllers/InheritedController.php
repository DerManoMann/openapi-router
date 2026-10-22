<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

use OpenApi\Spec as OA;
use Radebatz\OpenApi\Routing\Attributes\Middleware;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\BarMiddleware;

class InheritedController extends BaseApiController
{
    #[OA\Operation\Get(path: '/inherited', x: ['name' => 'inherited'])]
    #[OA\Response(response: 200, description: 'All good')]
    #[Middleware(names: [BarMiddleware::class])]
    public function inherited()
    {
        return FakeResponse::create('Inherited');
    }
}
