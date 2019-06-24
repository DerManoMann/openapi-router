<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Silex;

use PHPUnit\Framework\TestCase;

class SilexTest extends TestCase
{
    use CallsControllerTrait;

    public function testNamedRoute()
    {
        $this->assertNotNull($route = $this->getRouter()->get('getya'));
        $this->assertEquals('/getya', $route->getPath());
    }
}
