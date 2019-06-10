<?php

namespace Radebatz\OpenApi\Routing\Tests\Fixtures;

use Illuminate\Routing\Controller;
use Radebatz\OpenApi\Routing\Annotations as OAX;

/**
 * @OAX\Controller(
 *     prefix="foo",
 *     middleware={"auth"},
 *     @OA\Response(response=401, description="Not Authenticated")
 * )
 */
class ControllerAnnotationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/bar",
     *     operationId="bar",
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public static function bar($request, $response)
    {
        return $response->write('Bar bar');
    }

    /**
     * @OAX\Get(
     *     path="/barx",
     *     operationId="barx",
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public static function barx($request, $response)
    {
        return $response->write('Bar bar');
    }

    /**
     * @OAX\Get(
     *     path="/barxx",
     *     operationId="barxx",
     *     middleware={"ding"},
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public static function barxx($request, $response)
    {
        return $response->write('Bar bar');
    }

    /**
     * @OAX\Get(
     *     path="/getya",
     *     operationId="foo.getya",
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
