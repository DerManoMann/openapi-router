<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Controllers\Slim;

class ParametersController
{
    /**
     * @OA\Get(
     *     path="/hey/{name}",
     *     x={
     *       "name": "hey"
     *     },
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         required=true,
     *         description="The name",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function hey($request, $response, $name)
    {
        return $response->write(sprintf('Hey: %s', $name));
    }

    /**
     * @OA\Get(
     *     path="/oi/{name}",
     *     x={
     *       "name": "oi"
     *     },
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         required=false,
     *         description="The name",
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function oi($request, $response, $name = 'you')
    {
        return $response->write(sprintf('Oi: %s', $name));
    }

    /**
     * @OA\Get(
     *     path="/id/{id}",
     *     x={
     *       "name": "id"
     *     },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The id",
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function id($request, $response, $name)
    {
        return $response->write(sprintf('ID: %s', $id));
    }

    /**
     * @OA\Get(
     *     path="/hid/{hid}",
     *     x={
     *       "name": "hid"
     *     },
     *     @OA\Parameter(
     *         name="hid",
     *         in="path",
     *         required=true,
     *         description="The hid",
     *         @OA\Schema(
     *             type="string",
     *             pattern="[0-9a-f]+"
     *         )
     *     ),
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function hid($request, $response, $hid)
    {
        return $response->write(sprintf('HID: %s', $hid));
    }

    /**
     * @OA\Get(
     *     path="/multi/{foo}/{bar}",
     *     x={
     *       "name": "multi"
     *     },
     *     @OA\Parameter(
     *         name="foo",
     *         in="path",
     *         required=false,
     *         description="The foo",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="bar",
     *         in="path",
     *         required=false,
     *         description="The bar",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function multi($request, $response, $foo = null, $bar = null)
    {
        return $response->write('foobar');
    }
}
