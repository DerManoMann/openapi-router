<?php

namespace Radebatz\OpenApi\Routing\Annotations;

/**
 * @Annotation
 */
class Patch extends \OpenApi\Annotations\Patch
{
    use MiddlewareProperty;
}
