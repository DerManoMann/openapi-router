<?php

namespace Radebatz\OpenApi\Routing\Annotations;

use const OpenApi\Annotations\UNDEFINED;

trait MiddlewareProperty
{
    /**
     * Middlewares.
     *
     * @var array
     */
    public $middleware = UNDEFINED;
}
