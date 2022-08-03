<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class PropertyAssignToMethodCall
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
    private $oldPropertyName;
    /**
     * @readonly
     * @var string
     */
    private $newMethodName;
    /**
     * @param class-string $class
     */
    public function __construct(string $class, string $oldPropertyName, string $newMethodName)
    {
        $this->class = $class;
        $this->oldPropertyName = $oldPropertyName;
        $this->newMethodName = $newMethodName;
        RectorAssert::className($class);
        RectorAssert::propertyName($oldPropertyName);
        RectorAssert::methodName($newMethodName);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->class);
    }
    public function getOldPropertyName() : string
    {
        return $this->oldPropertyName;
    }
    public function getNewMethodName() : string
    {
        return $this->newMethodName;
    }
}
