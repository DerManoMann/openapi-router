<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Lumen;

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
        $response = $this->get($this->route('prefixed'));
        $this->assertEquals(200, $this->response->getStatusCode());

        $response = $this->get('foo/prefixed');
        $this->assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @test
     * @requires PHP 8.1
     */
    public function attributesPrefixed()
    {
        $response = $this->get('attributes/prefixed');
        $this->assertEquals(200, $this->response->getStatusCode());
    }
}
