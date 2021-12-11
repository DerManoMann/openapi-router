<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Annotations;

use OpenApi\Annotations\AbstractAnnotation;
use OpenApi\Annotations\Attachable;
use OpenApi\Annotations\Response;
use OpenApi\Generator;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class AbstractController extends AbstractAnnotation
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

if (\PHP_VERSION_ID >= 80100) {
    /**
     * @Annotation
     */
    #[\Attribute(\Attribute::TARGET_CLASS)]
    class Controller extends AbstractController
    {
        public function __construct(
            array $properties = [],
            string $prefix = Generator::UNDEFINED,
            $middleware = Generator::UNDEFINED,
            $x = Generator::UNDEFINED,
            $responses = Generator::UNDEFINED
        ) {
            parent::__construct($properties + [
                    'prefix' => $prefix,
                    'middleware' => $middleware,
                    'x' => $x,
                    'value' => $this->combine($responses),
                ]);
        }
    }
} else {
    /**
     * @Annotation
     */
    class Controller extends AbstractController
    {
        public function __construct(array $properties)
        {
            parent::__construct($properties);
        }
    }
}
