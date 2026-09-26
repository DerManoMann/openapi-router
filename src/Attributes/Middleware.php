<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Attributes;

use OpenApi\Spec\Attachable;
use OpenApi\Spec\Operation;
use OpenApi\Spec\PathItem;

/**
 * Attaches PSR-15 middleware to an operation or its containing controller.
 *
 * Stack beside an `Operation` (per-route) or a `PathItem` (per-controller). Controller-level
 * inheritance is resolved by `OpenApiRouter`, since swagger-php's `PathItems` augmenter does
 * not clone `attachables` down to operations. It follows the class hierarchy the same way a
 * `PathItem` prefix does, so a base controller's middleware applies to every subclass, in
 * order from the outermost ancestor down to the operation itself.
 *
 *   #[OA\PathItem(prefix: '/users')]
 *   #[Middleware(names: ['auth'])]
 *   class UserController
 *   {
 *       #[OA\Operation\Get(path: '/{id}')]
 *       #[Middleware(names: ['throttle'])]
 *       public function show(string $id) {}
 *   }
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Middleware extends Attachable
{
    /**
     * @param array<string> $names Middleware names or class-strings, as the adapter expects them
     */
    public function __construct(
        public array $names = [],
    ) {
    }

    /**
     * Nest into the `Operation` or `PathItem` this is stacked beside.
     *
     * Without it the attribute stays unmerged and is silently orphaned.
     *
     * @return array<class-string,string>
     */
    public function merge(): array
    {
        return [
            Operation::class => 'attachables[]',
            PathItem::class => 'attachables[]',
        ];
    }

    /**
     * Middleware only ever qualifies an operation or path item, never stands alone.
     *
     * Saying so turns a misplaced `#[Middleware]` into an assembly error.
     */
    public function isRoot(): bool
    {
        return false;
    }
}
