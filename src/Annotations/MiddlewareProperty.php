<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Annotations;

use OpenApi\Generator;

trait MiddlewareProperty
{
    /**
     * Middlewares.
     *
     * @var array
     */
    public $middleware = Generator::UNDEFINED;
}
