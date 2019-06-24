<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures;

/**
 * @OAX\Controller(
 *     prefix="foo",
 *     @OA\Response(response="403", description="Not allowed")
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
        return 'invoke';
    }
}
