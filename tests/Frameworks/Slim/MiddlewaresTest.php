<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Slim;

use PHPUnit\Framework\TestCase;

class MiddlewaresTest extends TestCase
{
    use CallsControllerTrait;

    /** @test */
    public function middleware()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('mw'));
        $this->assertEquals('/mw', $route->getPattern());
        $this->assertCount(2, $route->getMiddleware());
    }
}
