<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Validation\RectorAssert;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\AddParamTypeForFunctionLikeWithinCallLikeDeclarationRector\AddParamTypeForFunctionLikeWithinCallLikeDeclarationRectorTest
 */
final class AddParamTypeForFunctionLikeWithinCallLikeArgDeclaration
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
     * @var int<0, max>|string
     * @readonly
     */
    private $callLikePosition;
    /**
     * @var int<0, max>
     * @readonly
     */
    private $functionLikePosition;
    /**
     * @readonly
     * @var \PHPStan\Type\Type
     */
    private $paramType;
    /**
     * @param int<0, max>|string $callLikePosition
     * @param int<0, max> $functionLikePosition
     */
    public function __construct(string $className, string $methodName, $callLikePosition, int $functionLikePosition, Type $paramType)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->callLikePosition = $callLikePosition;
        $this->functionLikePosition = $functionLikePosition;
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
    /**
     * @return int<0, max>|string
     */
    public function getCallLikePosition()
    {
        return $this->callLikePosition;
    }
    /**
     * @return int<0, max>
     */
    public function getFunctionLikePosition() : int
    {
        return $this->functionLikePosition;
    }
    public function getParamType() : Type
    {
        return $this->paramType;
    }
}
