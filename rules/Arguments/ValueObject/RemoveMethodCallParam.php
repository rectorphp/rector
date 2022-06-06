<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Arguments\ValueObject;

use RectorPrefix20220606\PHPStan\Type\ObjectType;
final class RemoveMethodCallParam
{
    /**
     * @readonly
     * @var string
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
