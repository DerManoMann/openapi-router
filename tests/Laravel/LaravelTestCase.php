<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Laravel;

use PHPUnit\Framework\TestCase;

if (class_exists(\Illuminate\Foundation\Testing\TestCase::class)) {
    abstract class LaravelTestCase extends \Illuminate\Foundation\Testing\TestCase
    {
    }
} else {
    class LaravelTestCase extends TestCase
    {
    }
}
