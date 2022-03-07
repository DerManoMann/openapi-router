<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Lumen;

class LumenTest extends LumenTestCase
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

    /** @test */
    public function prefixed()
    {
        $this->get($this->route('prefixed'));
        $this->assertEquals(200, $this->response->getStatusCode());

        $this->get('foo/prefixed');
        $this->assertEquals(200, $this->response->getStatusCode());
    }

    /** @test */
    public function middleware()
    {
        $this->get($this->route('mw'));
        $this->assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @test
     * @requires PHP 8.1
     */
    public function attributesPrefixed()
    {
        $this->get('attributes/prefixed');
        $this->assertEquals(200, $this->response->getStatusCode());
    }
}
