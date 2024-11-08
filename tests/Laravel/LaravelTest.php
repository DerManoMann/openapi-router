<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Laravel;

use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\BarMiddleware;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\FooMiddleware;

class LaravelTest extends LaravelTestCase
{
    use CallsApplicationTrait;

    /** @test */
    public function namedRoute()
    {
        $this->assertNotNull($this->getRouter()->getRoutes()->getByName('getya'));
    }

    /** @test */
    public function invoke()
    {
        $this->assertNotNull($this->getRouter()->getRoutes()->getByName('invoke'));

        $response = $this->get($this->route('invoke', 'joe'));
        $response->assertStatus(200);
    }

    /** @test */
    public function prefixed()
    {
        $response = $this->get($this->route('prefixed'));
        $response->assertStatus(200);

        $response = $this->get('foo/prefixed');
        $response->assertStatus(200);
    }

    /**
     * @test
     *
     * @requires PHP 8.1
     */
    public function attributesPrefixed()
    {
        $response = $this->get('attributes/prefixed');
        echo $response->getContent();
        $response->assertStatus(200);
    }

    /**
     * @test
     *
     * @requires PHP 8.1
     */
    public function attributesMiddleware()
    {
        $route = $this->getRouter()->getRoutes()->getByName('attributes');

        $this->assertNotNull($route);
        $this->assertEquals([BarMiddleware::class, FooMiddleware::class], $route->gatherMiddleware());
    }
}
