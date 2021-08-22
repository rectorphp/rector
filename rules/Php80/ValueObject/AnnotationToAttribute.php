<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

final class AnnotationToAttribute
{
    /**
     * @var string
     */
    private $tag;
    /**
     * @var string|null
     */
    private $attributeClass;
    /**
     * @param class-string|string $tag
     * @param class-string $attributeClass
     */
    public function __construct(string $tag, ?string $attributeClass = null)
    {
        $this->tag = $tag;
        $this->attributeClass = $attributeClass;
    }
    /**
     * @return class-string|string
     */
    public function getTag() : string
    {
        return $this->tag;
    }
    /**
     * @return class-string
     */
    public function getAttributeClass() : string
    {
        if ($this->attributeClass === null) {
            return $this->tag;
        }
        return $this->attributeClass;
    }
}
