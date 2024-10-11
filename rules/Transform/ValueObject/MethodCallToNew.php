<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
final class MethodCallToNew
{
    /**
     * @readonly
     * @var \PHPStan\Type\ObjectType
     */
    private $objectType;
    /**
     * @readonly
     * @var string
     */
    private $methodName;
    /**
     * @var class-string
     * @readonly
     */
    private $newClassString;
    /**
     * @param class-string $newClassString
     */
    public function __construct(ObjectType $objectType, string $methodName, string $newClassString)
    {
        $this->objectType = $objectType;
        $this->methodName = $methodName;
        $this->newClassString = $newClassString;
    }
    public function getObject() : ObjectType
    {
        return $this->objectType;
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    public function getNewClassString() : string
    {
        return $this->newClassString;
    }
}
