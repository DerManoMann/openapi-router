<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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

    /**
     * @OA\Get(
     *     path="/invoke_getya",
     *     x={
     *       "name": "invoke_getya"
     *     },
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function __invoke(Request $request, Response $response)
    {
        $response->getBody()->write('Get ya');

        return $response;
    }
}
