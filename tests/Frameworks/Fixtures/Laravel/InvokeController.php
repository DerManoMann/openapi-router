<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Tests\Frameworks\Fixtures\Laravel;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * @OAX\Controller(
 *     prefix="foo",
 *     @OA\Response(response="403", description="Not allowed")
 * )
 */
class InvokeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/invoke",
     *     x={
     *       "name": "invoke"
     *     },
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function __invoke(Request $request)
    {
        return 'invoke';
    }
}
