<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Lumen;

use Laravel\Lumen\Testing\TestCase;

class LumenTest extends TestCase
{
    use CallsApplicationTrait;

    /** @test */
    public function namedRoute()
    {
        $this->assertTrue(array_key_exists('invoke', $this->getRouter()->namedRoutes));
    }

    /** @test */
    public function invoke()
    {
        $this->get('/foo/invoke/joe');
        $this->assertEquals(200, $this->response->getStatusCode());
    }
}
