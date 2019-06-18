<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Annotations;

/**
 * @Annotation
 */
class Get extends \OpenApi\Annotations\Get
{
    use MiddlewareProperty;
}
