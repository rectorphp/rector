<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
final class GetAndSetToMethodCall
{
    /**
     * @var class-string
     */
    private $classType;
    /**
     * @var string
     */
    private $getMethod;
    /**
     * @var string
     */
    private $setMethod;
    /**
     * @param class-string $classType
     */
    public function __construct(string $classType, string $getMethod, string $setMethod)
    {
        $this->classType = $classType;
        $this->getMethod = $getMethod;
        $this->setMethod = $setMethod;
    }
    public function getGetMethod() : string
    {
        return $this->getMethod;
    }
    public function getSetMethod() : string
    {
        return $this->setMethod;
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->classType);
    }
}
