<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures;

use Illuminate\Routing\Controller;

class SimpleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/getya",
     *     operationId="getya",
     *     x={
     *         "middleware"={"foo"},
     *     },
     *
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public static function getya($request, $response)
    {
        return $response->write('Get ya');
    }
}
