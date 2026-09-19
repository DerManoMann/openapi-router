<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Attributes;

use OpenApi\Spec\Attachable;
use OpenApi\Spec\Operation;
use OpenApi\Spec\PathItem;

/**
 * Attaches one or more PSR-15 middleware to an operation or its containing controller.
 *
 * Stack directly alongside an `Operation` attribute (per-route) or a `PathItem` attribute
 * (per-controller, resolved via `OpenApiRouter`'s own class-to-`PathItem` lookup — swagger-php's
 * `PathItems` augmenter clones `tags`/`security`/`responses` from a class-level `PathItem` onto
 * its operations, but not `attachables`, so this package resolves that inheritance itself):
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
     * @param array<string> $names PSR-15 middleware names/class-strings, in the shape the routing adapter expects
     */
    public function __construct(
        public array $names = [],
    ) {
    }

    /**
     * Without this, `Attachable::isRoot()` (true, unconditionally) means an unmerged
     * `Middleware` lands in `Specification::$attachables` as its own top-level, orphaned
     * entry instead of nesting into the `Operation`/`PathItem` it was stacked beside.
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
}
