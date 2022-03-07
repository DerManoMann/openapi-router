<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Lumen;

if (class_exists('\\Laravel\\Lumen\\Testing\\TestCase')) {
    abstract class LumenTestCase extends \Laravel\Lumen\Testing\TestCase
    {
    }
} else {
    class LumenTestCase extends \PHPUnit\Framework\TestCase
    {
    }
}
