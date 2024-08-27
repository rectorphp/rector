<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Validation\RectorAssert;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeFromArgRector\AddClosureParamTypeFromArgRectorTest
 */
final class AddClosureParamTypeFromArg
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
    private $callLikePosition;
    /**
     * @var int<0, max>
     * @readonly
     */
    private $functionLikePosition;
    /**
     * @var int<0, max>
     * @readonly
     */
    private $fromArgPosition;
    /**
     * @param int<0, max> $callLikePosition
     * @param int<0, max> $functionLikePosition
     * @param int<0, max> $fromArgPosition
     */
    public function __construct(string $className, string $methodName, int $callLikePosition, int $functionLikePosition, int $fromArgPosition)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->callLikePosition = $callLikePosition;
        $this->functionLikePosition = $functionLikePosition;
        $this->fromArgPosition = $fromArgPosition;
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
     * @return int<0, max>
     */
    public function getCallLikePosition() : int
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
    /**
     * @return int<0, max>
     */
    public function getFromArgPosition() : int
    {
        return $this->fromArgPosition;
    }
}
