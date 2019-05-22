<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Routing;

use OpenApi\Analysis;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;
use Psr\SimpleCache\CacheInterface;

/**
 * OpenApi router.
 */
class OpenApiRouter
{
    public const OPTION_RELOAD = 'relaod';
    public const OPTION_CACHE = 'cache';

    public const CACHE_KEY_OPENAPI = 'openapi-router.openapi';

    protected $sources = [];
    protected $routingAdapter;
    protected $options = [];

    /**
     * Create new routes.
     *
     * @param array                   $sources        Mixed list of either controller paths or instances of `OpenApi\Annotations\OpenApi`
     * @param RoutingAdapterInterface $routingAdapter the framework adapter
     * @param array                   $options        Optional configuration options
     */
    public function __construct(array $sources, RoutingAdapterInterface $routingAdapter, array $options = [])
    {
        $this->sources = $sources;
        $this->routingAdapter = $routingAdapter;
        $this->options = $options + [self::OPTION_RELOAD => true, self::OPTION_CACHE => null];
    }

    public function registerRoutes()
    {
        if (!$this->options[self::OPTION_RELOAD] && $this->routingAdapter->registerCached()) {
            return;
        }

        $openapis = null;
        /** @var CacheInterface $cache */
        if (($cache = $this->options[self::OPTION_CACHE]) && !$this->options[self::OPTION_RELOAD]) {
            // try cache
            $openapis = $cache->get(self::CACHE_KEY_OPENAPI);
        }

        array_map(function ($openapi) {
            $this->registerOpenApi($openapi);
        }, $openapis ?: ($openapis = $this->scan()));

        if ($cache && !$this->options[self::OPTION_RELOAD]) {
            $cache->set(self::CACHE_KEY_OPENAPI, $openapis);
        }
    }

    protected function registerOpenApi(OpenApi $openapi)
    {
        $methods = ['get', 'post', 'put', 'patch', 'delete', 'options', 'head'];

        foreach ($openapi->paths as $path) {
            foreach ($methods as $method) {
                $operation = null;
                /** @var Parameter[] $parameters */
                $parameters = [];

                if (\OpenApi\UNDEFINED !== $path->{$method}) {
                    /** @var Operation $operation */
                    $operation = $path->{$method};

                    if (\OpenApi\UNDEFINED !== $operation->parameters) {
                        foreach ($operation->parameters as $parameter) {
                            if ('path' == $parameter->in) {
                                $parameters[] = $parameter;
                            }
                        }
                    }

                    if ($operation) {
                        $custom = [
                            RoutingAdapterInterface::X_NAME => null,
                            RoutingAdapterInterface::X_MIDDLEWARE => [],
                        ];
                        if (\OpenApi\UNDEFINED !== $operation->x) {
                            foreach (array_keys($custom) as $xKey) {
                                if (array_key_exists($xKey, $operation->x)) {
                                    $custom[$xKey] = $operation->x[$xKey];
                                }
                            }
                        }

                        $this->routingAdapter->register($operation, array_reverse($parameters), $custom);
                    }
                }
            }
        }
    }

    public function scan(): array
    {
        // provide default @OA\Info in case we need to do some scanning
        $options = [
            'analysis' => new Analysis([new Info(['title' => 'Test', 'version' => '1.0'])]),
        ];

        return array_map(function ($source) use ($options) {
            return is_string($source) ? \OpenApi\scan($source, $options) : $source;
        }, $this->sources);
    }
}
