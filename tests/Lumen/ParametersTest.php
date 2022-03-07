<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Lumen;

class ParametersTest extends LumenTestCase
{
    use CallsApplicationTrait;

    /** @test */
    public function parameter()
    {
        $this->get($this->route('hey', ['name' => 'joe']));
        $this->assertEquals(200, $this->response->getStatusCode());
    }

    /** @test */
    public function optionalParameter()
    {
        $this->get($this->route('oi', ['name' => 'joe']));
        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertEquals('Oi: joe', $this->response->getContent());

        $this->get($this->route('oi'));
        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertEquals('Oi: you', $this->response->getContent());
    }

    /** @test */
    public function typedParameterMatch()
    {
        $this->get($this->route('id', ['id' => '123']));
        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertEquals('ID: 123', $this->response->getContent());
    }

    /** @test */
    public function typedParameterFail()
    {
        $this->get($this->route('id', ['id' => 'x123']));
        $this->assertEquals(404, $this->response->getStatusCode());
    }

    /** @test */
    public function regexParameterMatch()
    {
        $this->get($this->route('hid', ['hid' => 'a1b2c3']));
        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertEquals('HID: a1b2c3', $this->response->getContent());
    }

    /** @test */
    public function regexParameterFail()
    {
        $this->get($this->route('hid', ['hid' => 'za1b2c3']));
        $this->assertEquals(404, $this->response->getStatusCode());
    }
}
