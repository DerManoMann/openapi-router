<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

use OpenApi\Annotations as OA;

class NamedRouteController
{
    /**
     * @OA\Get(
     *     path="/getya",
     *     x={
     *         "name": "getya"
     *     },
     *
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function getya()
    {
        return FakeResponse::create('Get ya');
    }

    /**
     * @OA\Get(
     *     path="/static_getya",
     *     x={
     *       "name": "static_getya"
     *     },
     *
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public static function static_getya()
    {
        return FakeResponse::create('Static Get ya');
    }
}
