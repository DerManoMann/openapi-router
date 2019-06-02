<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Controllers\Slim;

class MiddlewareController
{
    /**
     * @OA\Get(
     *     path="/mw",
     *     x={
     *       "name": "mw",
     *       "middleware"={"FooMiddleware", "BarMiddleware"}
     *     },
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public static function mw($request, $response)
    {
        return $response->write('MW!');
    }
}
