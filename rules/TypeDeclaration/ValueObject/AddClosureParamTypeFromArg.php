<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Validation\RectorAssert;
final class AddClosureParamTypeFromArg
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
    private int $callLikePosition;
    /**
     * @var int<0, max>
     * @readonly
     */
    private int $functionLikePosition;
    /**
     * @param int<0, max> $callLikePosition
     * @param int<0, max> $functionLikePosition
     */
    public function __construct(string $className, string $methodName, int $callLikePosition, int $functionLikePosition)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->callLikePosition = $callLikePosition;
        $this->functionLikePosition = $functionLikePosition;
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
}
