<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Validation\RectorAssert;
final class ReplaceParentCallByPropertyCall
{
    /**
     * @readonly
     */
    private string $class;
    /**
     * @readonly
     */
    private string $method;
    /**
     * @readonly
     */
    private string $property;
    public function __construct(string $class, string $method, string $property)
    {
        $this->class = $class;
        $this->method = $method;
        $this->property = $property;
        RectorAssert::className($class);
        RectorAssert::methodName($method);
        RectorAssert::propertyName($property);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->class);
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    public function getProperty() : string
    {
        return $this->property;
    }
}
