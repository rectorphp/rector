<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use Rector\Core\Validation\RectorAssert;
use Rector\Php80\Contract\ValueObject\AnnotationToAttributeInterface;
final class AnnotationToAttribute implements AnnotationToAttributeInterface
{
    /**
     * @readonly
     * @var string
     */
    private $tag;
    /**
     * @readonly
     * @var string|null
     */
    private $attributeClass;
    public function __construct(string $tag, ?string $attributeClass = null)
    {
        $this->tag = $tag;
        $this->attributeClass = $attributeClass;
        RectorAssert::className($tag);
        if (\is_string($attributeClass)) {
            RectorAssert::className($attributeClass);
        }
    }
    public function getTag() : string
    {
        return $this->tag;
    }
    public function getAttributeClass() : string
    {
        if ($this->attributeClass === null) {
            return $this->tag;
        }
        return $this->attributeClass;
    }
}
