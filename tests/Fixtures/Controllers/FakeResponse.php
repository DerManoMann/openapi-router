<?php

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

use Nyholm\Psr7\Factory\Psr17Factory;
use Slim\Factory\AppFactory;

class FakeResponse
{
    public static function create($body, $response = null)
    {
        if ($response) {
            return $response->write('MW!');
        }

        if (class_exists(AppFactory::class)) {
            $response =  (new Psr17Factory())->createResponse();
            $response->getBody()->write($body);

            return $response;
        }

        return $body;
    }
}
