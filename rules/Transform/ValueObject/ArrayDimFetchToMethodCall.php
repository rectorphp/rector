<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
class ArrayDimFetchToMethodCall
{
    /**
     * @readonly
     */
    private ObjectType $objectType;
    /**
     * @readonly
     */
    private string $method;
    public function __construct(ObjectType $objectType, string $method)
    {
        $this->objectType = $objectType;
        $this->method = $method;
    }
    public function getObjectType() : ObjectType
    {
        return $this->objectType;
    }
    public function getMethod() : string
    {
        return $this->method;
    }
}
