<?php

declare(strict_types=1);

namespace Rector\Naming\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;

final class PropertyRename implements RenameValueObjectInterface
{
    /**
     * @var string
     */
    private $expectedName;

    /**
     * @var string
     */
    private $currentName;

    /**
     * @var Property
     */
    private $property;

    /**
     * @var ClassLike
     */
    private $classLike;

    /**
     * @var PropertyProperty
     */
    private $propertyProperty;

    /**
     * @var string
     */
    private $classLikeName;

    public function __construct(
        Property $property, string $expectedName, string $currentName, ClassLike $classLike, string $classLikeName, PropertyProperty $propertyProperty
    ) {
        $this->property = $property;
        $this->expectedName = $expectedName;
        $this->currentName = $currentName;
        $this->classLike = $classLike;
        $this->classLikeName = $classLikeName;
        $this->propertyProperty = $propertyProperty;
    }

    /**
     * @return Property
     */
    public function getNode(): Node
    {
        return $this->property;
    }

    public function getExpectedName(): string
    {
        return $this->expectedName;
    }

    public function getCurrentName(): string
    {
        return $this->currentName;
    }

    public function getClassLike(): ClassLike
    {
        return $this->classLike;
    }

    public function getClassLikeName(): string
    {
        return $this->classLikeName;
    }

    public function getPropertyProperty(): PropertyProperty
    {
        return $this->propertyProperty;
    }
}
