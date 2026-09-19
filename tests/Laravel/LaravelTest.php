<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Laravel;

use Illuminate\Routing\Route;
use PHPUnit\Framework\Attributes\Test;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\BarMiddleware;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\FooMiddleware;

final class LaravelTest extends LaravelTestCase
{
    use CallsApplicationTrait;

    #[Test]
    public function namedRoute(): void
    {
        $this->assertInstanceOf(Route::class, $this->getRouter()->getRoutes()->getByName('getya'));
    }

    #[Test]
    public function invoke(): void
    {
        $this->assertInstanceOf(Route::class, $this->getRouter()->getRoutes()->getByName('invoke'));

        $response = $this->get($this->route('invoke', 'joe'));
        $response->assertStatus(200);
    }

    #[Test]
    public function prefixed(): void
    {
        $response = $this->get($this->route('prefixed'));
        $response->assertStatus(200);

        $response = $this->get('foo/prefixed');
        $response->assertStatus(200);
    }

    #[Test]
    public function attributesPrefixed(): void
    {
        $response = $this->get('attributes/prefixed');
        echo $response->getContent();
        $response->assertStatus(200);
    }

    #[Test]
    public function attributesMiddleware(): void
    {
        $route = $this->getRouter()->getRoutes()->getByName('attributes');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame([FooMiddleware::class, BarMiddleware::class], $route->gatherMiddleware());
    }
}
