<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

final class AnnotationToAttribute
{
    /**
     * @param class-string|string $tag
     * @param class-string $attributeClass
     */
    public function __construct(
        private string $tag,
        private string $attributeClass
    ) {
    }

    /**
     * @return class-string|string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @return class-string
     */
    public function getAttributeClass(): string
    {
        return $this->attributeClass;
    }
}
