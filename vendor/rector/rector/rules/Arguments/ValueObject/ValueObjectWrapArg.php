<?php

declare (strict_types=1);
namespace Rector\Arguments\ValueObject;

use PHPStan\Type\ObjectType;
final class ValueObjectWrapArg
{
    /**
     * @var string
     */
    private $objectType;
    /**
     * @var string
     */
    private $methodName;
    /**
     * @var int
     */
    private $argPosition;
    /**
     * @var string
     */
    private $newType;
    public function __construct(string $objectType, string $methodName, int $argPosition, string $newType)
    {
        $this->objectType = $objectType;
        $this->methodName = $methodName;
        $this->argPosition = $argPosition;
        $this->newType = $newType;
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->objectType);
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    public function getArgPosition() : int
    {
        return $this->argPosition;
    }
    public function getNewType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->newType);
    }
}
