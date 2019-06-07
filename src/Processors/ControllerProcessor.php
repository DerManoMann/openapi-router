<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\AbstractAnnotation;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\PathItem;
use OpenApi\Context;
use Radebatz\OpenApi\Routing\Annotations\Controller;
use Radebatz\OpenApi\Routing\Annotations\MiddlewareProperty;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;
use const OpenApi\Annotations\UNDEFINED;

/**
 * Merge shared controller properties.
 */
class ControllerProcessor
{
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
                        // update path
                        if (UNDEFINED !== $controller->prefix) {
                            $pathItem->path = str_replace('//', '/', $controller->prefix . '/' . $pathItem->path);
                        }

                        // now update all operations for this pathItem
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

                $this->clearMerged($analysis, $controller);
                $this->clearMerged($analysis, $controller->responses);
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

    protected function clearMerged(Analysis $analysis, $annotations)
    {
        if (UNDEFINED === $annotations) {
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
