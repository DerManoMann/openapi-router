<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

use OpenApi\Spec as OA;

class ParametersController
{
    #[OA\Operation\Get(path: '/hey/{name}', x: ['name' => 'hey'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function hey(
        #[OA\Parameter\Path(description: 'The name', schema: new OA\Schema(type: 'string'))]
        $name
    ) {
        return FakeResponse::create(sprintf('Hey: %s', $name));
    }

    #[OA\Operation\Get(path: '/oi/{name}', x: ['name' => 'oi'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function oi(
        #[OA\Parameter\Path(description: 'The name', required: false, schema: new OA\Schema(type: 'string'))]
        $name = 'you'
    ) {
        return FakeResponse::create(sprintf('Oi: %s', $name));
    }

    #[OA\Operation\Get(path: '/id/{id}', x: ['name' => 'id'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function id(
        #[OA\Parameter\Path(description: 'The id', schema: new OA\Schema(type: 'integer', format: 'int32'))]
        $id
    ) {
        return FakeResponse::create(sprintf('ID: %s', $id));
    }

    #[OA\Operation\Get(path: '/hid/{hid}', x: ['name' => 'hid'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function hid(
        #[OA\Parameter\Path(description: 'The hid', schema: new OA\Schema(type: 'string', pattern: '[0-9a-f]+'))]
        $hid
    ) {
        return FakeResponse::create(sprintf('HID: %s', $hid));
    }

    #[OA\Operation\Get(path: '/multi/{foo}/{bar}', x: ['name' => 'multi'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function multi(
        #[OA\Parameter\Path(description: 'The foo', required: false, schema: new OA\Schema(type: 'string'))]
        $foo = null,
        #[OA\Parameter\Path(description: 'The bar', required: false, schema: new OA\Schema(type: 'string'))]
        $bar = null
    ) {
        return FakeResponse::create('foobar');
    }
}
