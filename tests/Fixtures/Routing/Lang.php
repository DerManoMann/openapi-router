<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Fixtures\Routing;

use OpenApi\Spec as OA;

#[OA\Schema(type: 'string')]
enum Lang: string
{
    case En = 'en';
    case De = 'de';
}
