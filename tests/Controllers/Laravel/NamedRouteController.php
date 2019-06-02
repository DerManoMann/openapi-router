<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Controllers\Laravel;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NamedRouteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/getya",
     *     x={
     *       "name": "getya"
     *     },
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function __invoke(Request $request)
    {
        return 'getya';
    }
}
