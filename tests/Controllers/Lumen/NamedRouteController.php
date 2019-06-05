<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Controllers\Laravel;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class NamedRouteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/getya",
     *     operationId="getya",
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function __invoke(Request $request, $name)
    {
        return response('getya');
    }
}
