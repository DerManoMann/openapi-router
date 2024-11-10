<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers;

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\BarMiddleware;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\FooMiddleware;

#[OAX\Controller(prefix: '/attributes')]
#[OAT\Response(response: 403, description: 'Not allowed')]
#[OAX\Middleware([FooMiddleware::class])]
class AttributeController
{
    #[OAT\Get(path: '/prefixed', x: ['name' => 'attributes'])]
    #[OAT\Response(response: 200, description: 'All good')]
    #[OAX\Middleware([BarMiddleware::class])]
    public function prefixed()
    {
        return FakeResponse::create('Get fooya');
    }
}
