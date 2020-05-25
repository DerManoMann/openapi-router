<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Annotations;

/**
 * @Annotation
 */
class Trace extends \OpenApi\Annotations\Trace
{
    use MiddlewareProperty;
}
