<?php

declare (strict_types=1);
namespace Rector\Naming\ValueObject;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Naming\Contract\RenamePropertyValueObjectInterface;
final class PropertyRename implements \Rector\Naming\Contract\RenamePropertyValueObjectInterface
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
    public function __construct(\PhpParser\Node\Stmt\Property $property, string $expectedName, string $currentName, \PhpParser\Node\Stmt\ClassLike $classLike, string $classLikeName, \PhpParser\Node\Stmt\PropertyProperty $propertyProperty)
    {
        $this->property = $property;
        $this->expectedName = $expectedName;
        $this->currentName = $currentName;
        $this->classLike = $classLike;
        $this->classLikeName = $classLikeName;
        $this->propertyProperty = $propertyProperty;
    }
    public function getProperty() : \PhpParser\Node\Stmt\Property
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
    public function getClassLike() : \PhpParser\Node\Stmt\ClassLike
    {
        return $this->classLike;
    }
    public function getClassLikeName() : string
    {
        return $this->classLikeName;
    }
    public function getPropertyProperty() : \PhpParser\Node\Stmt\PropertyProperty
    {
        return $this->propertyProperty;
    }
}
