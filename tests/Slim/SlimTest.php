<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Slim;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Routing\Adapters\SlimRoutingAdapter;

final class SlimTest extends TestCase
{
    use CallsControllerTrait;

    #[Test]
    public function namedRoute(): void
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('getya'));
        $this->assertSame('/getya', $route->getPattern());
    }

    #[Test]
    public function request(): void
    {
        $response = $this->call('/static_getya');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals('Static Get ya', $response->getBody());
    }

    #[Test]
    public function prefixed(): void
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('prefixed'));

        $response = $this->call('/foo/prefixed');
        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function middleware(): void
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('mw'));

        $response = $this->call('/mw');
        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function attributesPrefixed(): void
    {
        $response = $this->call('/attributes/prefixed');
        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function hasNoRouteCacheOfItsOwn(): void
    {
        // Slim has no route cache to load, so the scan always runs
        $this->assertFalse((new SlimRoutingAdapter($this->getApp()))->registerCached());
    }

    #[Test]
    public function inheritedPathItem(): void
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('inherited'));
        $this->assertSame('/base/inherited', $route->getPattern());
    }
}
