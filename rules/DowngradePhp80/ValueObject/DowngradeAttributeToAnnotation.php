<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\ValueObject;

final class DowngradeAttributeToAnnotation
{
    /**
     * @param class-string $attributeClass
     * @param class-string|string $tag
     */
    public function __construct(
        private string $attributeClass,
        private string $tag
    ) {
    }

    public function getAttributeClass(): string
    {
        return $this->attributeClass;
    }

    public function getTag(): string
    {
        return $this->tag;
    }
}
