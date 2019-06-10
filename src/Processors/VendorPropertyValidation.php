<?php

namespace Radebatz\OpenApi\Routing\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Operation;
use Radebatz\OpenApi\Routing\Annotations\MiddlewareProperty;
use Radebatz\OpenApi\Routing\RoutingAdapterInterface;

/**
 * Validation of vendor (this)  properties.
 */
class VendorPropertyValidation
{
    protected $names = [];
    protected $uniqueNames = true;
    protected $callback;

    /**
     * @param null $callback    callable to validate custom proeprty values.
     *                          Called for each value of the property if an `array`.
     * @param bool $uniqueNames Flag to enable/disable checking names for uniqueness
     */
    public function __construct($callback = null, bool $uniqueNames = true)
    {
        $this->uniqueNames = $uniqueNames;
        if ($callback && !is_callable($callback)) {
            throw new \InvalidArgumentException('Invalid callback');
        }
        $this->callback = $callback;
    }

    public function __invoke(Analysis $analysis)
    {
        $allOperations = $analysis->getAnnotationsOfType(Operation::class);
        /** @var Operation $operation */
        foreach ($allOperations as $operation) {
            if (\OpenApi\UNDEFINED !== $operation->operationId) {
                $this->validateUniqueName($operation->operationId);
            }

            $uses = array_flip(class_uses_recursive($operation));
            if (array_key_exists(MiddlewareProperty::class, $uses)) {
                if (\OpenApi\UNDEFINED !== $operation->middleware) {
                    $this->validateVendorProperty(RoutingAdapterInterface::X_MIDDLEWARE, $operation->middleware);
                }
            }

            if (\OpenApi\UNDEFINED !== $operation->x) {
                if (array_key_exists(RoutingAdapterInterface::X_NAME, $operation->x)) {
                    $this->validateUniqueName($operation->x[RoutingAdapterInterface::X_NAME]);
                }

                $custom = [RoutingAdapterInterface::X_MIDDLEWARE, RoutingAdapterInterface::X_NAME];
                foreach ($custom as $key) {
                    if (array_key_exists($key, $operation->x)) {
                        $this->validateVendorProperty($key, $operation->x[$key]);
                    }
                }
            }
        }
    }

    protected function validateUniqueName($name)
    {
        if ($this->uniqueNames && in_array($name, $this->names)) {
            throw new \LogicException(sprintf('Duplicate operationId/name: "%s"', $name));
        }
        $this->names[] = $name;
    }

    protected function validateVendorProperty($name, $value)
    {
        if ($this->callback) {
            $values = (array) $value;
            foreach ($values as $value) {
                call_user_func($this->callback, $name, $value);
            }
        }
    }
}
