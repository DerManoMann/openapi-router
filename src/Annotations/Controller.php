<?php

namespace Radebatz\OpenApi\Routing\Annotations;

use OpenApi\Annotations\AbstractAnnotation;
use const OpenApi\Annotations\UNDEFINED;

/**
 * @Annotation
 */
class Controller extends AbstractAnnotation
{
    use MiddlewareProperty;

    /**
     * A shared path prefix for all uris in this controller.
     *
     * @var string
     */
    public $prefix = UNDEFINED;

    /**
     * The list of possible responses as they are returned from executing this operation.
     *
     * @var \OpenApi\Annotations\Response[]
     */
    public $responses = UNDEFINED;

    /**
     * @inheritdoc
     */
    public static $_nested = [
        'OpenApi\Annotations\Response' => ['responses', 'response'],
    ];
}
