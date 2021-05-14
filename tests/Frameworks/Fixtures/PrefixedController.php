<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures;

/**
 * @OAX\Controller(
 *     prefix="foo",
 *     @OA\Response(response="403", description="Not allowed")
 * )
 */
class PrefixedController
{
    /**
     * @OA\Get(
     *     path="/prefixed",
     *     x={
     *       "name": "prefixed"
     *     },
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function prefixed()
    {
        return FakeResponse::create('Get fooya');
    }
}
