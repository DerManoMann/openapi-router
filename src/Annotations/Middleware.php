<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Annotations;

use OpenApi\Annotations\Attachable;
use OpenApi\Annotations\Operation;
use OpenApi\Generator;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_ALL | \Attribute::IS_REPEATABLE)]
class Middleware extends Attachable
{
    /**
     * The middleware names.
     *
     * @var array
     */
    public $names = Generator::UNDEFINED;

    /**
     * @inheritdoc
     */
    public static $_required = ['names'];

    /**
     * @inheritdoc
     */
    public function allowedParents(): ?array
    {
        return [Operation::class, Controller::class];
    }

    public function __construct(array $names)
    {
        parent::__construct([]);
        $this->names = $names;
    }
}
