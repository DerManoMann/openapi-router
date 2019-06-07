<?php

namespace Radebatz\OpenApi\Routing\Annotations;

/**
 * @Annotation
 */
class Patch extends \OpenApi\Annotations\Get
{
    use MiddlewareProperty;
}
