<?php

declare (strict_types=1);
namespace Rector\Arguments\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Validation\RectorAssert;
final class RemoveMethodCallParam
{
    /**
     * @readonly
     */
    private string $class;
    /**
     * @readonly
     */
    private string $methodName;
    /**
     * @readonly
     */
    private int $paramPosition;
    public function __construct(string $class, string $methodName, int $paramPosition)
    {
        $this->class = $class;
        $this->methodName = $methodName;
        $this->paramPosition = $paramPosition;
        RectorAssert::className($class);
        RectorAssert::methodName($methodName);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->class);
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    public function getParamPosition() : int
    {
        return $this->paramPosition;
    }
}
