<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Annotations;

use OpenApi\Annotations\AbstractAnnotation;
use OpenApi\Annotations\Attachable;
use OpenApi\Annotations\Response;
use OpenApi\Generator;

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
    public $prefix = Generator::UNDEFINED;

    /**
     * The list of possible responses as they are returned from executing this operation.
     *
     * @var \OpenApi\Annotations\Response[]
     */
    public $responses = Generator::UNDEFINED;

    /**
     * @inheritdoc
     */
    public static $_nested = [
        Response::class => ['responses', 'response'],
        Attachable::class => ['attachables'],
    ];
}
