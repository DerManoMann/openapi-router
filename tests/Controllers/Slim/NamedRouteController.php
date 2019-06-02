<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Controllers\Slim;

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
    public static function getya($request, $response)
    {
        return $response->write('Get ya');
    }
}
