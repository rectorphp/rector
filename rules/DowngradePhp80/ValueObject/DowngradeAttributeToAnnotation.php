<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\ValueObject;

final class DowngradeAttributeToAnnotation
{
    /**
     * @var class-string
     */
    private $attributeClass;
    /**
     * @var class-string|string|null
     */
    private $tag;
    /**
     * @param class-string $attributeClass
     * @param class-string|string|null $tag
     */
    public function __construct(string $attributeClass, ?string $tag = null)
    {
        $this->attributeClass = $attributeClass;
        $this->tag = $tag;
    }
    public function getAttributeClass() : string
    {
        return $this->attributeClass;
    }
    public function getTag() : string
    {
        if ($this->tag === null) {
            return $this->attributeClass;
        }
        return $this->tag;
    }
}
