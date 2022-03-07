<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

/**
 * @OAX\Controller(
 *     prefix="/foo",
 *     @OA\Response(response="401", description="Unauthorized")
 * )
 */
class InvokeController
{
    /**
     * @OA\Get(
     *     path="/invoke/{name}",
     *     x={
     *       "name": "invoke"
     *     },
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function __invoke($name)
    {
        return FakeResponse::create('invoke');
    }
}
