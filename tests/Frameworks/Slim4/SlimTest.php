<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Slim4;

use PHPUnit\Framework\TestCase;

class SlimTest extends TestCase
{
    use CallsControllerTrait;

    /** @test */
    public function namedRoute()
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('getya'));
        $this->assertEquals('/getya', $route->getPattern());
    }

    /** @test */
    public function request()
    {
        $response = $this->call('/static_getya');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Static Get ya', $response->getBody());
    }

    /** @test */
    public function prefixed()
    {
        $this->assertNotNull($route = $this->getRouteCollector()->getNamedRoute('prefixed'));

        $response = $this->call('/foo/prefixed');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     * @requires PHP 8.1
     */
    public function attributesPrefixed()
    {
        $response = $this->call('attributes/prefixed');
        $this->assertEquals(200, $response->getStatusCode());
    }
}
