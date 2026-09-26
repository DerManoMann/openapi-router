<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Routing;

use OpenApi\Spec as OA;

/**
 * Names its operation only through `operationId`, so the route name depends entirely on
 * `setOperationIdAsName()`.
 *
 * Deliberately outside `Fixtures/Controllers`: the framework tests scan that directory and
 * expect every route there to be named.
 */
class OperationIdController
{
    #[OA\Operation\Get(path: '/widgets', operationId: 'listWidgets')]
    #[OA\Response(response: 200, description: 'All good')]
    public function list()
    {
    }
}
