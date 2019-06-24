<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Silex;

use PHPUnit\Framework\TestCase;

class ParametersTest extends TestCase
{
    use CallsControllerTrait;

    public function testParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->get('hey'));
        $this->assertEquals('/hey/{name}', $route->getPath());
    }

    public function testTypedParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->get('id'));
        $this->assertEquals(['id' => '[0-9]+'], $route->getRequirements());
    }

    public function testRegexParameter()
    {
        $this->assertNotNull($route = $this->getRouter()->get('hid'));
        $this->assertEquals(['hid' => '[0-9a-f]+'], $route->getRequirements());
    }
}
