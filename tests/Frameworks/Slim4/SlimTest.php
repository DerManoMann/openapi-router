<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Slim4;

use PHPUnit\Framework\TestCase;
use Slim\Http\Request;

class SlimTest extends TestCase
{
    use CallsControllerTrait;

    /** @test */
    public function namedRoute()
    {
        $this->assertNotNull($route = $this->getRouteCollection()->getNamedRoute('invoke_getya'));
        $this->assertEquals('/invoke_getya', $route->getPattern());
    }

    /** @test */
    public function request()
    {
        $response = $this->call('/invoke_getya');
        $this->assertEquals(200, $response->getStatusCode());
    }
}
