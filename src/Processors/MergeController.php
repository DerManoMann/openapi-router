<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\AbstractAnnotation;
use OpenApi\Annotations\Operation;
use OpenApi\Context;
use OpenApi\Generator;
use Radebatz\OpenApi\Routing\Annotations\Controller;
use Radebatz\OpenApi\Routing\Annotations\Middleware;
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
                        if (Generator::UNDEFINED !== $controller->prefix) {
                            $path = $controller->prefix . '/' . $operation->path;
                            $operation->path = str_replace('//', '/', $path);
                        }

                        if (Generator::UNDEFINED !== $controller->middleware || Generator::UNDEFINED !== $controller->attachables) {
                            $middleware = Generator::UNDEFINED !== $controller->middleware ? $controller->middleware : [];
                            if (Generator::UNDEFINED !== $controller->attachables) {
                                foreach ($controller->attachables as $attachable) {
                                    if ($attachable instanceof Middleware) {
                                        $middleware = array_merge($middleware, $controller->names);
                                    }
                                }
                            }
                            $middleware = array_unique($middleware);

                            $uses = array_flip(class_uses($operation));
                            if (array_key_exists(MiddlewareProperty::class, $uses)) {
                                $operation->middleware = Generator::UNDEFINED !== $operation->middleware ? $operation->middleware : [];
                                $operation->middleware = array_merge($operation->middleware, $middleware);
                            } else {
                                // add as X property
                                $operation->x = Generator::UNDEFINED !== $operation->x ? $operation->x : [];
                                $operation->x[RoutingAdapterInterface::X_MIDDLEWARE] =
                                    array_key_exists(RoutingAdapterInterface::X_MIDDLEWARE, $operation->x)
                                        ? $operation->x[RoutingAdapterInterface::X_MIDDLEWARE]
                                        : [];
                                $operation->x[RoutingAdapterInterface::X_MIDDLEWARE] =
                                    array_merge($operation->x[RoutingAdapterInterface::X_MIDDLEWARE], $middleware);
                            }
                        }

                        if (Generator::UNDEFINED !== $controller->responses) {
                            $operation->merge($controller->responses, true);
                        }
                    }
                }
            }
        }
    }

    protected function needsProcessing(Controller $controller)
    {
        return Generator::UNDEFINED !== $controller->prefix
            || Generator::UNDEFINED !== $controller->middleware
            || Generator::UNDEFINED !== $controller->responses;
    }

    protected function contextMatch(AbstractAnnotation $annotation, Context $context)
    {
        return $annotation->_context->namespace === $context->namespace
            && $annotation->_context->class == $context->class;
    }
}
