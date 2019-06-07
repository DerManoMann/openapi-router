<?php

namespace Radebatz\OpenApi\Routing\Annotations;

/**
 * @Annotation
 */
class Head extends \OpenApi\Annotations\Get
{
    use MiddlewareProperty;
}
