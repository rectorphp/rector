<?php

declare(strict_types=1);

namespace Rector\PostRector\ValueObject;

final class PropertyMetadata
{
    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var \PHPStan\Type\Type|null
     */
    private $propertyType;

    /**
     * @var int
     */
    private $propertyFlags;

    public function __construct(string $propertyName, ?\PHPStan\Type\Type $propertyType, int $propertyFlags)
    {
        $this->propertyName = $propertyName;
        $this->propertyType = $propertyType;
        $this->propertyFlags = $propertyFlags;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getPropertyType(): ?\PHPStan\Type\Type
    {
        return $this->propertyType;
    }

    public function getPropertyFlags(): int
    {
        return $this->propertyFlags;
    }
}
