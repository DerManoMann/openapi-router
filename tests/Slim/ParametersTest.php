<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Slim;

use PHPUnit\Framework\TestCase;

class ParametersTest extends TestCase
{
    use CallsControllerTrait;

    /** @test */
    public function parameter()
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('hey'));
        $this->assertEquals('/hey/{name}', $route->getPattern());
    }

    /** @test */
    public function optionalParameter()
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('oi'));
        $this->assertEquals('/oi[/{name}]', $route->getPattern());
    }

    /** @test */
    public function multiOptionalParameter()
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('multi'));
        $this->assertEquals('/multi[/{foo}[/{bar}]]', $route->getPattern());
    }

    /** @test */
    public function typedParameter()
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('id'));
        $this->assertEquals('/id/{id:[0-9]+}', $route->getPattern());
    }

    /** @test */
    public function regexParameter()
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('hid'));
        $this->assertEquals('/hid/{hid:[0-9a-f]+}', $route->getPattern());
    }
}
