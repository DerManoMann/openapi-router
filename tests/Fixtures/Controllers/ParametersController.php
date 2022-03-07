<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

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
    public function hey($name)
    {
        return FakeResponse::create(sprintf('Hey: %s', $name));
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
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function oi($name = 'you')
    {
        return FakeResponse::create(sprintf('Oi: %s', $name));
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
    public function id($id)
    {
        return FakeResponse::create(sprintf('ID: %s', $id));
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
    public function hid($hid)
    {
        return FakeResponse::create(sprintf('HID: %s', $hid));
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
    public function multi($foo = null, $bar = null)
    {
        return FakeResponse::create('foobar');
    }
}
