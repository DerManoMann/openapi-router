<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Routing;

use OpenApi\Spec as OA;

/**
 * A path parameter declared once as a component, for `RefParameterController` to refer to.
 */
#[OA\Components]
#[OA\Parameter\Path(name: 'tenant', description: 'The tenant', required: true, schema: new OA\Schema(type: 'integer'))]
class TenantParameter
{
}
