<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

final class MethodCallToFuncCall
{
    /**
     * @readonly
     */
    private string $objectType;
    /**
     * @readonly
     */
    private string $methodName;
    /**
     * @readonly
     */
    private string $functionName;
    public function __construct(string $objectType, string $methodName, string $functionName)
    {
        $this->objectType = $objectType;
        $this->methodName = $methodName;
        $this->functionName = $functionName;
    }
    public function getObjectType() : string
    {
        return $this->objectType;
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    public function getFunctionName() : string
    {
        return $this->functionName;
    }
}
