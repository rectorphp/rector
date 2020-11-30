<?php

declare(strict_types=1);

namespace Rector\Naming\ValueObject;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Naming\Contract\RenamePropertyValueObjectInterface;

final class PropertyRename implements RenamePropertyValueObjectInterface
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
     * @var string
     */
    private $classLikeName;

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

    public function __construct(
        Property $property,
        string $expectedName,
        string $currentName,
        ClassLike $classLike,
        string $classLikeName,
        PropertyProperty $propertyProperty
    ) {
        $this->property = $property;
        $this->expectedName = $expectedName;
        $this->currentName = $currentName;
        $this->classLike = $classLike;
        $this->classLikeName = $classLikeName;
        $this->propertyProperty = $propertyProperty;
    }

    public function getProperty(): Property
    {
        return $this->property;
    }

    public function isPrivateProperty(): bool
    {
        return $this->property->isPrivate();
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
