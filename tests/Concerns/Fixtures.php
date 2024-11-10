<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Concerns;

use Symfony\Component\Finder\Finder;

trait Fixtures
{
    public function getFixtureFinder(): Finder
    {
        $controllersDir = __DIR__ . '/../Fixtures/Controllers';

        return (new Finder())
            ->files()
            ->followLinks()
            ->name('*.php')
            ->in($controllersDir);
    }
}
