<?php

namespace Radebatz\OpenApi\Routing\Annotations;

/**
 * @Annotation
 */
class Options extends \OpenApi\Annotations\Get
{
    use MiddlewareProperty;
}
