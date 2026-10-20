<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Slim;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ParametersTest extends TestCase
{
    use CallsControllerTrait;

    #[Test]
    public function parameter(): void
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('hey'));
        $this->assertSame('/hey/{name}', $route->getPattern());
    }

    #[Test]
    public function optionalParameter(): void
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('oi'));
        $this->assertSame('/oi[/{name}]', $route->getPattern());
    }

    #[Test]
    public function multiOptionalParameter(): void
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('multi'));
        $this->assertSame('/multi[/{foo}[/{bar}]]', $route->getPattern());
    }

    #[Test]
    public function pathItemParameter(): void
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('shared'));
        $this->assertSame('/shared/{tenant:[0-9]+}/items', $route->getPattern());
    }

    #[Test]
    public function typedParameter(): void
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('id'));
        $this->assertSame('/id/{id:[0-9]+}', $route->getPattern());
    }

    #[Test]
    public function regexParameter(): void
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('hid'));
        $this->assertSame('/hid/{hid:[0-9a-f]+}', $route->getPattern());
    }

    #[Test]
    public function enumParameter(): void
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('lang'));
        $this->assertSame('/lang/{lang:en|de}', $route->getPattern());
    }

    #[Test]
    public function uuidParameter(): void
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('item'));
        $this->assertSame('/item/{id:[\\da-fA-F]{8}-[\\da-fA-F]{4}-[\\da-fA-F]{4}-[\\da-fA-F]{4}-[\\da-fA-F]{12}}', $route->getPattern());
    }

    #[Test]
    public function defaultParameter(): void
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('list'));
        $this->assertSame('/list[/{page:[0-9]+}]', $route->getPattern());
        $this->assertSame('1', $route->getArgument('page'));
    }
}
