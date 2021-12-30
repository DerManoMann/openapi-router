<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\AbstractAnnotation;
use OpenApi\Annotations\Operation;
use OpenApi\Context;
use OpenApi\Generator;
use Radebatz\OpenApi\Routing\Annotations\Controller;
use Radebatz\OpenApi\Routing\Annotations\Middleware as AnnotationMiddleware;
use Radebatz\OpenApi\Routing\Attributes\Middleware as AttributeMiddleware;
use Radebatz\OpenApi\Routing\Annotations\MiddlewareProperty;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;

/**
 * Update operation path if controller prefix given.
 */
class MergeController
{
    public function __invoke(Analysis $analysis)
    {
        /** @var Controller[] $controllers */
        $controllers = $analysis->getAnnotationsOfType(Controller::class);
        /** @var Operation[] $operations */
        $operations = $analysis->getAnnotationsOfType(Operation::class);

        foreach ($controllers as $controller) {
            if ($this->needsProcessing($controller)) {
                foreach ($operations as $operation) {
                    if ($this->contextMatch($operation, $controller->_context)) {
                        // update path
                        if (!Generator::isDefault($controller->prefix)) {
                            $path = $controller->prefix . '/' . $operation->path;
                            $operation->path = str_replace('//', '/', $path);
                        }

                        if (!Generator::isDefault($controller->middleware) || !Generator::isDefault($controller->attachables)) {
                            $middleware = !Generator::isDefault($controller->middleware) ? $controller->middleware : [];
                            if (!Generator::isDefault($controller->attachables)) {
                                foreach ($controller->attachables as $attachable) {
                                    if ($attachable instanceof AnnotationMiddleware || $attachable instanceof AttributeMiddleware) {
                                        $middleware = array_merge($middleware, $attachable->names);
                                    }
                                }
                            }
                            if (!Generator::isDefault($operation->attachables)) {
                                foreach ($operation->attachables as $attachable) {
                                    if ($attachable instanceof AnnotationMiddleware || $attachable instanceof AttributeMiddleware) {
                                        $middleware = array_merge($middleware, $attachable->names);
                                    }
                                }
                            }
                            $middleware = array_unique($middleware);

                            $uses = array_flip(class_uses($operation));
                            if (array_key_exists(MiddlewareProperty::class, $uses)) {
                                $operation->middleware = !Generator::isDefault($operation->middleware) ? $operation->middleware : [];
                                $operation->middleware = array_merge($operation->middleware, $middleware);
                            } else {
                                // add as X property
                                $operation->x = !Generator::isDefault($operation->x) ? $operation->x : [];
                                $operation->x[RoutingAdapterInterface::X_MIDDLEWARE] =
                                    array_key_exists(RoutingAdapterInterface::X_MIDDLEWARE, $operation->x)
                                        ? $operation->x[RoutingAdapterInterface::X_MIDDLEWARE]
                                        : [];
                                $operation->x[RoutingAdapterInterface::X_MIDDLEWARE] =
                                    array_merge($operation->x[RoutingAdapterInterface::X_MIDDLEWARE], $middleware);
                            }
                        }

                        if (!Generator::isDefault($controller->responses)) {
                            $operation->merge($controller->responses, true);
                        }
                    }
                }
            }
        }
    }

    protected function needsProcessing(Controller $controller)
    {
        return !Generator::isDefault($controller->prefix)
            || !Generator::isDefault($controller->middleware)
            || !Generator::isDefault($controller->responses)
            || !Generator::isDefault($controller->attachables);
    }

    protected function contextMatch(AbstractAnnotation $annotation, Context $context)
    {
        return $annotation->_context->namespace === $context->namespace
            && $annotation->_context->class == $context->class;
    }
}
