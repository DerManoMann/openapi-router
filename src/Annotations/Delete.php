<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Annotations;

/**
 * @Annotation
 */
class Delete extends \OpenApi\Annotations\Delete
{
    use MiddlewareProperty;
}
