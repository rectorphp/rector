<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\ValueObject;

final class DowngradeAttributeToAnnotation
{
    /**
     * @param class-string $attributeClass
     * @param class-string|string|null $tag
     */
    public function __construct(
        private readonly string $attributeClass,
        private readonly ?string $tag = null
    ) {
    }

    public function getAttributeClass(): string
    {
        return $this->attributeClass;
    }

    public function getTag(): string
    {
        if ($this->tag === null) {
            return $this->attributeClass;
        }

        return $this->tag;
    }
}
