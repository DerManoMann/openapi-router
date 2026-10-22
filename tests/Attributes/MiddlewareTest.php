<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Attributes;

use OpenApi\Assembler;
use OpenApi\OpenApiException;
use OpenApi\Spec as OA;
use OpenApi\Specification;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Routing\Attributes\Middleware;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Controllers\AttributeController;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Invalid\MisplacedMiddleware;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\BarMiddleware;
use Radebatz\OpenApi\Routing\Tests\Fixtures\Middleware\FooMiddleware;

/**
 * Covers `Middleware` nesting, which is convention-driven rather than enforced by a type.
 *
 * A missing `merge()` or `isRoot()` does not fail — it silently produces empty middleware
 * lists, so the behaviour needs pinning directly.
 */
final class MiddlewareTest extends TestCase
{
    /**
     * @param class-string $class
     */
    private function assemble(string $class): Specification
    {
        $assembler = new Assembler();
        $assembler->collect(new \ReflectionClass($class));

        return $assembler->getSpecification();
    }

    public function testMergesIntoTheOperationItIsStackedOn(): void
    {
        $specification = $this->assemble(AttributeController::class);

        $this->assertCount(1, $specification->operations);
        $names = $this->middlewareNames($specification->operations[0]->attachables ?? []);

        $this->assertSame([BarMiddleware::class], $names);
    }

    public function testMergesIntoTheContainingPathItem(): void
    {
        $specification = $this->assemble(AttributeController::class);

        $this->assertCount(1, $specification->pathItems);
        $names = $this->middlewareNames($specification->pathItems[0]->attachables ?? []);

        $this->assertSame([FooMiddleware::class], $names);
    }

    /**
     * An unmergeable instance must fail rather than vanish.
     *
     * Nothing merged it and it is not a root, so silently landing in
     * `Specification::$attachables` would hide the mistake.
     */
    public function testMisplacedMiddlewareIsRejected(): void
    {
        $this->expectException(OpenApiException::class);
        $this->expectExceptionMessageMatches('/Non-root attribute .*Middleware/');

        $this->assemble(MisplacedMiddleware::class);
    }

    public function testIsNeverARootAttribute(): void
    {
        $this->assertFalse((new Middleware())->isRoot());
    }

    /**
     * @param list<OA\Attachable> $attachables
     *
     * @return list<string>
     */
    private function middlewareNames(array $attachables): array
    {
        $names = [];
        foreach ($attachables as $attachable) {
            if ($attachable instanceof Middleware) {
                $names = array_merge($names, $attachable->names);
            }
        }

        return array_values($names);
    }
}
