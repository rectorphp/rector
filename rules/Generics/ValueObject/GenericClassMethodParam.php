<?php

declare (strict_types=1);
namespace Rector\Generics\ValueObject;

final class GenericClassMethodParam
{
    /**
     * @readonly
     * @var string
     */
    private $classType;
    /**
     * @readonly
     * @var string
     */
    private $methodName;
    /**
     * @readonly
     * @var int
     */
    private $paramPosition;
    /**
     * @readonly
     * @var string
     */
    private $paramGenericType;
    public function __construct(string $classType, string $methodName, int $paramPosition, string $paramGenericType)
    {
        $this->classType = $classType;
        $this->methodName = $methodName;
        $this->paramPosition = $paramPosition;
        $this->paramGenericType = $paramGenericType;
    }
    public function getClassType() : string
    {
        return $this->classType;
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    public function getParamPosition() : int
    {
        return $this->paramPosition;
    }
    public function getParamGenericType() : string
    {
        return $this->paramGenericType;
    }
}
