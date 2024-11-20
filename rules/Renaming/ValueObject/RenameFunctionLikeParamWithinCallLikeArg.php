<?php

declare (strict_types=1);
namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Validation\RectorAssert;
final class RenameFunctionLikeParamWithinCallLikeArg
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
     * @var int<0, max>|string
     * @readonly
     */
    private $callLikePosition;
    /**
     * @var int<0, max>
     * @readonly
     */
    private int $functionLikePosition;
    /**
     * @readonly
     */
    private string $newParamName;
    /**
     * @param int<0, max>|string $callLikePosition
     * @param int<0, max> $functionLikePosition
     */
    public function __construct(string $className, string $methodName, $callLikePosition, int $functionLikePosition, string $newParamName)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->callLikePosition = $callLikePosition;
        $this->functionLikePosition = $functionLikePosition;
        $this->newParamName = $newParamName;
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
    public function getNewParamName() : string
    {
        return $this->newParamName;
    }
}
