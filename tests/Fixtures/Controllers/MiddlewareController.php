<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

use OpenApi\Spec as OA;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\BarMiddleware;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\FooMiddleware;

class MiddlewareController
{
    #[OA\Operation\Get(
        path: '/mw',
        x: [
            'name' => 'mw',
            'middleware' => [FooMiddleware::class, BarMiddleware::class],
        ],
    )]
    #[OA\Response(response: 200, description: 'All good')]
    public function mw($request = null, $response = null)
    {
        return FakeResponse::create('MW!', $response);
    }
}
