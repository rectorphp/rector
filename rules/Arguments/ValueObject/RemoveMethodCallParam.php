<?php

declare (strict_types=1);
namespace Rector\Arguments\ValueObject;

use PHPStan\Type\ObjectType;
final class RemoveMethodCallParam
{
    /**
     * @var class-string
     * @readonly
     */
    private $class;
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
     * @param class-string $class
     */
    public function __construct(string $class, string $methodName, int $paramPosition)
    {
        $this->class = $class;
        $this->methodName = $methodName;
        $this->paramPosition = $paramPosition;
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
