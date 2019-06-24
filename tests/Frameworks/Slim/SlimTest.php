<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Slim;

use PHPUnit\Framework\TestCase;
use Slim\Http\Request;

class SlimTest extends TestCase
{
    use CallsControllerTrait;

    /** @test */
    public function namedRoute()
    {
        $this->assertNotNull($route = $this->getRouter()->getNamedRoute('getya'));
        $this->assertEquals('/getya', $route->getPattern());
    }

    /** @test */
    public function request()
    {
        $response = $this->call('/static_getya');
        $this->assertEquals(200, $response->getStatusCode());
    }
}
