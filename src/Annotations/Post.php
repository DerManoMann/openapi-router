<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing\Annotations;

/**
 * @Annotation
 */
class Post extends \OpenApi\Annotations\Post
{
    use MiddlewareProperty;
}
