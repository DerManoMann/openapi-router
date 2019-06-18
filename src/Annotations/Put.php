<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Annotations;

/**
 * @Annotation
 */
class Put extends \OpenApi\Annotations\Put
{
    use MiddlewareProperty;
}
