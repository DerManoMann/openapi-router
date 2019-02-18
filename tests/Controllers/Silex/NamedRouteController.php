<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Controllers\Silex;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function getya(Application $app, Request $request)
    {
        return new Response('Get ya');
    }
}
