<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Annotations;

/**
 * @Annotation
 */
class Options extends \OpenApi\Annotations\Options
{
    use MiddlewareProperty;
}
