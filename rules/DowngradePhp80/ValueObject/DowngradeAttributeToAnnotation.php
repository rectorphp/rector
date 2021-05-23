<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\ValueObject;

final class DowngradeAttributeToAnnotation
{
    /**
     * @var string
     */
    private $attributeClass;
    /**
     * @var string
     */
    private $tag;
    /**
     * @param class-string $attributeClass
     * @param class-string|string $tag
     */
    public function __construct(string $attributeClass, string $tag)
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
        return $this->tag;
    }
}
