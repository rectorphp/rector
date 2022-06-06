<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Transform\ValueObject;

use RectorPrefix20220606\PHPStan\Type\ObjectType;
final class CallableInMethodCallToVariable
{
    /**
     * @readonly
     * @var string
     */
    private $classType;
    /**
     * @readonly
     * @var string
     */
    private $methodName;
    /**
     * @readonly
     * @var int
     */
    private $argumentPosition;
    public function __construct(string $classType, string $methodName, int $argumentPosition)
    {
        $this->classType = $classType;
        $this->methodName = $methodName;
        $this->argumentPosition = $argumentPosition;
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->classType);
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    public function getArgumentPosition() : int
    {
        return $this->argumentPosition;
    }
}
