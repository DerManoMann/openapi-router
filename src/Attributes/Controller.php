<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Attributes;

use OpenApi\Attributes\Attachable;
use OpenApi\Attributes\Response;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Controller extends \Radebatz\OpenApi\Routing\Annotations\Controller
{

    /**
     * The list of possible responses as they are returned from executing this operation.
     *
     * @var Response[]
     */
    public $responses = Generator::UNDEFINED;

    /**
     * @inheritdoc
     */
    public static $_nested = [
        Response::class => ['responses', 'response'],
        Attachable::class => ['attachables'],
    ];

    /**
     * @param array<Response>|null   $responses
     * @param array<Attachable>|null $attachables
     */
    public function __construct(
        ?string $prefix = null,
        ?array $responses = null,
        // annotation
        ?array $x = null,
        ?array $attachables = null
    ) {
        parent::__construct([
            'prefix' => $prefix ?? Generator::UNDEFINED,
            'x' => $x ?? Generator::UNDEFINED,
            'value' => $this->combine($responses, $attachables),
        ]);
    }
}
