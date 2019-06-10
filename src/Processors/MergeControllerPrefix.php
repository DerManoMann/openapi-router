<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Operation;
use Radebatz\OpenApi\Routing\Annotations\Controller;
use const OpenApi\Annotations\UNDEFINED;

/**
 * Update operation path if controller prefix given.
 */
class MergeControllerPrefix
{
    use AnalysisTools;

    public function __invoke(Analysis $analysis)
    {
        $controllers = $analysis->getAnnotationsOfType(Controller::class);
        $operations = $analysis->getAnnotationsOfType(Operation::class);

        /** @var Controller $controller */
        foreach ($controllers as $controller) {
            if ($this->needsProcessing($controller)) {
                /** @var Operation $operation */
                foreach ($operations as $operation) {
                    if ($this->contextMatch($operation, $controller->_context)) {
                        // update path
                        if (UNDEFINED !== $controller->prefix) {
                            $path = $controller->prefix . '/' . $operation->path;
                            $operation->path = str_replace('//', '/', $path);
                        }
                    }
                }
            }
        }
    }
}
