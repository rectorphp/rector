<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Validation\RectorAssert;
final class AddParamTypeDeclaration
{
    /**
     * @readonly
     */
    private string $className;
    /**
     * @readonly
     */
    private string $methodName;
    /**
     * @var int<0, max>
     * @readonly
     */
    private int $position;
    /**
     * @readonly
     */
    private Type $paramType;
    /**
     * @param int<0, max> $position
     */
    public function __construct(string $className, string $methodName, int $position, Type $paramType)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->position = $position;
        $this->paramType = $paramType;
        RectorAssert::className($className);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->className);
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    public function getPosition() : int
    {
        return $this->position;
    }
    public function getParamType() : Type
    {
        return $this->paramType;
    }
}
