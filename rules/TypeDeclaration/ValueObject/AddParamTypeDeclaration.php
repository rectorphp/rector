<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Validation\RectorAssert;
final class AddParamTypeDeclaration
{
    /**
     * @readonly
     * @var string
     */
    private $className;
    /**
     * @readonly
     * @var string
     */
    private $methodName;
    /**
     * @var int<0, max>
     * @readonly
     */
    private $position;
    /**
     * @readonly
     * @var \PHPStan\Type\Type
     */
    private $paramType;
    /**
     * @param int<0, max> $position
     */
    public function __construct(string $className, string $methodName, int $position, \PHPStan\Type\Type $paramType)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->position = $position;
        $this->paramType = $paramType;
        \Rector\Core\Validation\RectorAssert::className($className);
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
