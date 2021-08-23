<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
final class AddParamTypeDeclaration
{
    /**
     * @var string
     */
    private $className;
    /**
     * @var string
     */
    private $methodName;
    /**
     * @var int
     */
    private $position;
    /**
     * @var \PHPStan\Type\Type
     */
    private $paramType;
    public function __construct(string $className, string $methodName, int $position, \PHPStan\Type\Type $paramType)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->position = $position;
        $this->paramType = $paramType;
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->className);
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    public function getPosition() : int
    {
        return $this->position;
    }
    public function getParamType() : \PHPStan\Type\Type
    {
        return $this->paramType;
    }
}
