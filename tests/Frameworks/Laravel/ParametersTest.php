<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Laravel;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class ParametersTest extends LaravelTestCase
{
    use CallsApplicationTrait;

    /** @test */
    public function parameter()
    {
        /* @var Route $route */
        $this->assertNotNull($route = $this->getRouter()->getRoutes()->getByName('hey'));
        $this->assertEquals('hey/{name}', $route->uri());
    }

    /** @test */
    public function optionalParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->getRoutes()->getByName('oi'));
        $this->assertEquals('oi/{name?}', $route->uri());
    }

    /** @test */
    public function typedParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->getRoutes()->getByName('id'));
        $this->assertTrue($route->matches(Request::create('id/123')));
        $this->assertFalse($route->matches(Request::create('id/12x3')));
    }

    /** @test */
    public function regexParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->getRoutes()->getByName('hid'));
        $this->assertTrue($route->matches(Request::create('hid/a1b2c3')));
        $this->assertFalse($route->matches(Request::create('hid/z12x3')));
    }
}
