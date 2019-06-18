<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Annotations;

/**
 * @Annotation
 */
class Head extends \OpenApi\Annotations\Head
{
    use MiddlewareProperty;
}
