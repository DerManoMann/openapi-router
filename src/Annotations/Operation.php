<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Annotations;

use OpenApi\Annotations\PathItem;
use OpenApi\Generator;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
abstract class AbstractOperation extends \OpenApi\Annotations\Operation
{
    use MiddlewareProperty;

    /**
     * @inheritdoc
     */
    public $method = 'options';

    /**
     * @inheritdoc
     */
    public static $_parents = [
        PathItem::class,
    ];
}

if (\PHP_VERSION_ID >= 80100) {
    /**
     * @Annotation
     */
    #[\Attribute(\Attribute::TARGET_CLASS)]
    class Operation extends AbstractOperation
    {
        public function __construct(
            array $properties = [],
            $middleware = Generator::UNDEFINED,
            $x = Generator::UNDEFINED
        ) {
            parent::__construct($properties + [
                    'middleware' => $middleware,
                    'x' => $x,
                ]);
        }
    }
} else {
    /**
     * @Annotation
     */
    class Operation extends AbstractOperation
    {
        public function __construct(array $properties)
        {
            parent::__construct($properties);
        }
    }
}
