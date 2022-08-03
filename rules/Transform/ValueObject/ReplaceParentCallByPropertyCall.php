<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class ReplaceParentCallByPropertyCall
{
    /**
     * @var class-string
     * @readonly
     */
    private $class;
    /**
     * @readonly
     * @var string
     */
    private $method;
    /**
     * @readonly
     * @var string
     */
    private $property;
    /**
     * @param class-string $class
     */
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
