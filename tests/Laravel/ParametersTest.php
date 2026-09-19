<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Laravel;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PHPUnit\Framework\Attributes\Test;

final class ParametersTest extends LaravelTestCase
{
    use CallsApplicationTrait;

    #[Test]
    public function parameter(): void
    {
        /* @var Route $route */
        $this->assertInstanceOf(Route::class, $route = $this->getRouter()->getRoutes()->getByName('hey'));
        $this->assertEquals('hey/{name}', $route->uri());
    }

    #[Test]
    public function optionalParameter(): void
    {
        $this->assertInstanceOf(Route::class, $route = $this->getRouter()->getRoutes()->getByName('oi'));
        $this->assertEquals('oi/{name?}', $route->uri());
    }

    #[Test]
    public function typedParameter(): void
    {
        $this->assertInstanceOf(Route::class, $route = $this->getRouter()->getRoutes()->getByName('id'));
        $this->assertTrue($route->matches(Request::create('id/123')));
        $this->assertFalse($route->matches(Request::create('id/12x3')));
    }

    #[Test]
    public function regexParameter(): void
    {
        $this->assertInstanceOf(Route::class, $route = $this->getRouter()->getRoutes()->getByName('hid'));
        $this->assertTrue($route->matches(Request::create('hid/a1b2c3')));
        $this->assertFalse($route->matches(Request::create('hid/z12x3')));
    }
}
