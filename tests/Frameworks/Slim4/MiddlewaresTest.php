<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Slim4;

use PHPUnit\Framework\TestCase;

class MiddlewaresTest extends TestCase
{
    use CallsControllerTrait;

    /** @test */
    public function middleware()
    {
        $this->assertNotNull($route = $this->getRouteCollection()->getNamedRoute('mw'));
        $this->assertEquals('/mw', $route->getPattern());
        // TODO: figure out how to count middlewares...
    }
}
