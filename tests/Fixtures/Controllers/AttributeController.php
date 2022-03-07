<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

use OpenApi\Attributes as OA;
use Radebatz\OpenApi\Routing\Attributes as OAX;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\BarMiddleware;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\FooMiddleware;

if (\PHP_VERSION_ID >= 80100) {
    #[OAX\Controller(prefix: '/attributes')]
    #[OA\Response(response: 403, description: 'Not allowed')]
    #[OAX\Middleware([FooMiddleware::class])]
    class AttributeController
    {
        #[OA\Get(path: '/prefixed', x: ['name' => 'attributes'])]
        #[OA\Response(response: 200, description: 'All good')]
        #[OAX\Middleware([BarMiddleware::class])]
        public function prefixed()
        {
            return FakeResponse::create('Get fooya');
        }
    }
}
