<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\PathItem;
use Radebatz\OpenApi\Routing\Annotations\Controller;
use Radebatz\OpenApi\Routing\Annotations\MiddlewareProperty;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;
use const OpenApi\Annotations\UNDEFINED;

/**
 * Merge shared controller properties.
 */
class MergeSharedProperties
{
    use AnalysisTools;

    public function __invoke(Analysis $analysis)
    {
        $methods = ['get', 'post', 'put', 'patch', 'delete', 'options', 'head'];
        $controllers = $analysis->getAnnotationsOfType(Controller::class);
        $pathItems = $analysis->getAnnotationsOfType(PathItem::class);

        /** @var Controller $controller */
        foreach ($controllers as $controller) {
            if ($this->needsProcessing($controller)) {
                /** @var PathItem $pathItem */
                foreach ($pathItems as $pathItem) {
                    if ($this->contextMatch($pathItem, $controller->_context)) {
                        // update all operations for this pathItem
                        foreach ($methods as $method) {
                            if (UNDEFINED !== $pathItem->{$method}) {
                                /** @var Operation $operation */
                                $operation = $pathItem->{$method};

                                // update path here too!
                                $operation->path = $pathItem->path;

                                if (UNDEFINED !== $controller->middleware) {
                                    $uses = array_flip(class_uses_recursive($operation));
                                    if (array_key_exists(MiddlewareProperty::class, $uses)) {
                                        $operation->middleware = UNDEFINED !== $operation->middleware ? $operation->middleware : [];
                                        $operation->middleware = array_merge($operation->middleware, $controller->middleware);
                                    } else {
                                        // add as X property
                                        $operation->x = UNDEFINED !== $operation->x ? $operation->x : [];
                                        $operation->x[RoutingAdapterInterface::X_MIDDLEWARE] =
                                            array_key_exists(RoutingAdapterInterface::X_MIDDLEWARE, $operation->x)
                                                ? $operation->x[RoutingAdapterInterface::X_MIDDLEWARE]
                                                : [];
                                        $operation->x[RoutingAdapterInterface::X_MIDDLEWARE] =
                                            array_merge($operation->x[RoutingAdapterInterface::X_MIDDLEWARE], $controller->middleware);
                                    }
                                }

                                if (UNDEFINED !== $controller->responses) {
                                    $operation->merge($controller->responses, true);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
