<?php

declare(strict_types=1);

namespace Rector\Naming\ValueObject;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;

final class PropertyRename extends Property
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
     * @var Type
     */
    private $propertyType;

    /**
     * @var ?ClassLike
     */
    private $classLike;

    public function __construct(
        Property $property,
        string $expectedName,
        string $currentName,
        Type $type,
        ?ClassLike $classLike
    ) {
        $this->property = $property;
        $this->expectedName = $expectedName;
        $this->currentName = $currentName;
        $this->propertyType = $type;
        $this->classLike = $classLike;
    }

    public function getProperty(): Property
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

    public function getPropertyType(): Type
    {
        return $this->propertyType;
    }

    public function getClassLike(): ?ClassLike
    {
        return $this->classLike;
    }
}
