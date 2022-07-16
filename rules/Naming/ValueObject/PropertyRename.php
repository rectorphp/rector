<?php

declare (strict_types=1);
namespace Rector\Naming\ValueObject;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Validation\RectorAssert;
use Rector\Naming\Contract\RenamePropertyValueObjectInterface;
final class PropertyRename implements RenamePropertyValueObjectInterface
{
    /**
     * @readonly
     * @var \PhpParser\Node\Stmt\Property
     */
    private $property;
    /**
     * @readonly
     * @var string
     */
    private $expectedName;
    /**
     * @readonly
     * @var string
     */
    private $currentName;
    /**
     * @readonly
     * @var \PhpParser\Node\Stmt\ClassLike
     */
    private $classLike;
    /**
     * @readonly
     * @var string
     */
    private $classLikeName;
    /**
     * @readonly
     * @var \PhpParser\Node\Stmt\PropertyProperty
     */
    private $propertyProperty;
    public function __construct(Property $property, string $expectedName, string $currentName, ClassLike $classLike, string $classLikeName, PropertyProperty $propertyProperty)
    {
        $this->property = $property;
        $this->expectedName = $expectedName;
        $this->currentName = $currentName;
        $this->classLike = $classLike;
        $this->classLikeName = $classLikeName;
        $this->propertyProperty = $propertyProperty;
        // name must be valid
        RectorAssert::propertyName($currentName);
        RectorAssert::propertyName($expectedName);
    }
    public function getProperty() : Property
    {
        return $this->property;
    }
    public function isPrivateProperty() : bool
    {
        return $this->property->isPrivate();
    }
    public function getExpectedName() : string
    {
        return $this->expectedName;
    }
    public function getCurrentName() : string
    {
        return $this->currentName;
    }
    public function isAlreadyExpectedName() : bool
    {
        return $this->currentName === $this->expectedName;
    }
    public function getClassLike() : ClassLike
    {
        return $this->classLike;
    }
    public function getClassLikeName() : string
    {
        return $this->classLikeName;
    }
    public function getPropertyProperty() : PropertyProperty
    {
        return $this->propertyProperty;
    }
}
