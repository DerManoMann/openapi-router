<?php declare(strict_types=1);

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
