<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures;

class NamedRouteController
{
    /**
     * @OA\Get(
     *     path="/getya",
     *     x={
     *       "name": "getya"
     *     },
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function getya()
    {
        return 'Get ya';
    }

    /**
     * @OA\Get(
     *     path="/static_getya",
     *     x={
     *       "name": "static_getya"
     *     },
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public static function static_getya()
    {
        return 'Get ya';
    }
}
