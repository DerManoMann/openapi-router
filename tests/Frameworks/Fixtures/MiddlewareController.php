<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures;

class MiddlewareController
{
    /**
     * @OA\Get(
     *     path="/mw",
     *     x={
     *       "name": "mw",
     *       "middleware"={"Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures\FooMiddleware", "Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures\BarMiddleware"}
     *     },
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function mw($request, $response)
    {
        return FakeResponse::create('MW!', $response);
    }
}
