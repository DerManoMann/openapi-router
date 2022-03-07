<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

class MiddlewareController
{
    /**
     * @OA\Get(
     *     path="/mw",
     *     x={
     *       "name": "mw",
     *       "middleware"={"Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\FooMiddleware", "Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\BarMiddleware"}
     *     },
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function mw($request = null, $response = null)
    {
        return FakeResponse::create('MW!', $response);
    }
}
