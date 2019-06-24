<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Lumen;

use Laravel\Lumen\Testing\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParametersTest extends TestCase
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
        if (false !== (strpos($this->createApplication()->version(), '5.7'))) {
            $this->markTestSkipped();
        }

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
        $this->expectException(NotFoundHttpException::class);
        $this->get($this->route('id', ['id' => 'x123']));
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
        $this->expectException(NotFoundHttpException::class);
        $this->get($this->route('hid', ['hid' => 'za1b2c3']));
    }
}
