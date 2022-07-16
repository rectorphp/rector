<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class MethodCallToPropertyFetch
{
    /**
     * @readonly
     * @var string
     */
    private $oldType;
    /**
     * @readonly
     * @var string
     */
    private $oldMethod;
    /**
     * @readonly
     * @var string
     */
    private $newProperty;
    public function __construct(string $oldType, string $oldMethod, string $newProperty)
    {
        $this->oldType = $oldType;
        $this->oldMethod = $oldMethod;
        $this->newProperty = $newProperty;
        RectorAssert::className($oldType);
        RectorAssert::methodName($oldMethod);
        RectorAssert::propertyName($newProperty);
    }
    public function getOldObjectType() : ObjectType
    {
        return new ObjectType($this->oldType);
    }
    public function getNewProperty() : string
    {
        return $this->newProperty;
    }
    public function getOldMethod() : string
    {
        return $this->oldMethod;
    }
}
