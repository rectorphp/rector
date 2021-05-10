<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class VariableMethodCallToServiceCall
{
    /**
     * @param mixed $argumentValue
     */
    public function __construct(
        private string $variableType,
        private string $methodName,
        private $argumentValue,
        private string $serviceType,
        private string $serviceMethodName
    ) {
    }

    public function getVariableObjectType(): ObjectType
    {
        return new ObjectType($this->variableType);
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return mixed
     */
    public function getArgumentValue()
    {
        return $this->argumentValue;
    }

    public function getServiceType(): string
    {
        return $this->serviceType;
    }

    public function getServiceMethodName(): string
    {
        return $this->serviceMethodName;
    }
}
