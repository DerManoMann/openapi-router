<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Processors;

use OpenApi\Analysis;
use OpenApi\Generator;
use Radebatz\OpenApi\Routing\Annotations\Controller;

/**
 * Clean up controller annotations.
 */
class ControllerCleanup
{
    public function __invoke(Analysis $analysis)
    {
        $controllers = $analysis->getAnnotationsOfType(Controller::class);
        foreach ($controllers as $controller) {
            $this->clearMerged($analysis, $controller);
            $this->clearMerged($analysis, $controller->responses);
        }
    }

    protected function clearMerged(Analysis $analysis, $annotations)
    {
        if (Generator::UNDEFINED === $annotations) {
            return;
        }

        $annotations = is_array($annotations) ? $annotations : [$annotations];

        foreach ($annotations as $annotation) {
            if (false !== ($offset = array_search($annotation, $analysis->openapi->_unmerged))) {
                unset($analysis->openapi->_unmerged[$offset]);
            }
        }
    }
}
