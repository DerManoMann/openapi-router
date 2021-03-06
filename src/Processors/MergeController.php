<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\AbstractAnnotation;
use OpenApi\Annotations\Operation;
use OpenApi\Context;
use Radebatz\OpenApi\Routing\Annotations\Controller;
use const OpenApi\Annotations\UNDEFINED;
use Radebatz\OpenApi\Routing\Annotations\MiddlewareProperty;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;

/**
 * Update operation path if controller prefix given.
 */
class MergeController
{
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

    protected function needsProcessing(Controller $controller)
    {
        return UNDEFINED !== $controller->prefix
            || UNDEFINED !== $controller->middleware
            || UNDEFINED !== $controller->responses;
    }

    protected function contextMatch(AbstractAnnotation $annotation, Context $context)
    {
        return $annotation->_context->namespace === $context->namespace
            && $annotation->_context->class == $context->class;
    }
}
