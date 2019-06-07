<?php

namespace Radebatz\OpenApi\Routing\Tests\Fixtures;

use Illuminate\Routing\Controller;

/**
 * @OAX\Controller(
 *     prefix="foo",
 *
 *     @OA\Response(response=401, description="Not Authenticated")
 * )
 */
class ControllerAnnotationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/bar",
     *     operationId="bar",
     *
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public static function bar($request, $response)
    {
        return $response->write('Bar bar');
    }
}
