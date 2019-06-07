<?php

namespace Radebatz\OpenApi\Routing\Annotations;

/**
 * @Annotation
 */
class Put extends \OpenApi\Annotations\Get
{
    use MiddlewareProperty;
}
