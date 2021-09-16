<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Routing\Annotations as OAX;

#[OAX\Controller(prefix: '/attributes')]
#[OA\Response(response: 403, description: 'Not allowed')]
class AttributeController
{
    #[OA\Get(path: '/prefixed', x: ['name' => 'prefixed'])]
    #[OA\Response(response: 200, description: 'All good')]
    public function prefixed()
    {
        return FakeResponse::create('Get fooya');
    }
}
