<?php

namespace Radebatz\OpenApi\Routing\Processors;

use OpenApi\Annotations\AbstractAnnotation;
use const OpenApi\Annotations\UNDEFINED;
use OpenApi\Context;
use Radebatz\OpenApi\Routing\Annotations\Controller;

trait AnalysisTools
{
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
