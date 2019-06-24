<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Laravel;

use Illuminate\Foundation\Testing\TestCase;

class LaravelTest extends TestCase
{
    use CallsApplicationTrait;

    /** @test */
    public function namedRoute()
    {
        $this->assertNotNull($this->getRouter()->getRoutes()->getByName('getya'));
    }

    /** @test */
    public function invoke()
    {
        $this->assertNotNull($this->getRouter()->getRoutes()->getByName('invoke'));

        $response = $this->get($this->route('invoke', 'joe'));
        $response->assertStatus(200);
    }

    /** @test */
    public function prefixed()
    {
        $response = $this->get($this->route('prefixed'));

        $response->assertStatus(200);

        $response = $this->get('foo/prefixed');

        $response->assertStatus(200);
    }
}
