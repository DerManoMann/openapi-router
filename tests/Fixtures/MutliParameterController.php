<?php

namespace Radebatz\OpenApi\Routing\Tests\Fixtures;

use Illuminate\Routing\Controller;

class MutliParameterController extends Controller
{
    /**
     * @OA\Get(
     *     path="/ding/{ding}/dong/{dong}",
     *     operationId="dingdong",
     *
     *     @OA\Parameter(
     *         name="foo",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="bar",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public static function foobar($ding, $dong)
    {
        return sprintf('%s the %s', $ding, $dong);
    }
}
