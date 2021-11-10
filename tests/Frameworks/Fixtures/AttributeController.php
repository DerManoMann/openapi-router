<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Routing\Annotations as OAX;

if (\PHP_VERSION_ID >= 80100) {
    #[OAX\Controller(prefix: '/attributes', middleware: [AMiddleware::class])]
    #[OA\Response(response: 403, description: 'Not allowed')]
    #[OAX\Middleware([CMiddleware::class])]
    class AttributeController
    {
        #[OA\Get(path: '/prefixed', x: ['name' => 'attributes'])]
        #[OA\Response(response: 200, description: 'All good')]
        #[OAX\Middleware([BMiddleware::class])]
        public function prefixed()
        {
            return FakeResponse::create('Get fooya');
        }
    }
}
