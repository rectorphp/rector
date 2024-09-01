<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use Rector\Php80\Contract\ValueObject\AnnotationToAttributeInterface;
use Rector\Validation\RectorAssert;
use RectorPrefix202409\Webmozart\Assert\Assert;
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
    /**
     * @var string[]
     * @readonly
     */
    private $classReferenceFields = [];
    /**
     * @param string[] $classReferenceFields
     */
    public function __construct(string $tag, ?string $attributeClass = null, array $classReferenceFields = [])
    {
        $this->tag = $tag;
        $this->attributeClass = $attributeClass;
        $this->classReferenceFields = $classReferenceFields;
        RectorAssert::className($tag);
        if (\is_string($attributeClass)) {
            RectorAssert::className($attributeClass);
        }
        Assert::allString($classReferenceFields);
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
    /**
     * @return string[]
     */
    public function getClassReferenceFields() : array
    {
        return $this->classReferenceFields;
    }
}
