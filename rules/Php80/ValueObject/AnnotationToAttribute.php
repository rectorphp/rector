<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use Rector\Php80\Contract\ValueObject\AnnotationToAttributeInterface;
use Rector\Validation\RectorAssert;
use RectorPrefix202506\Webmozart\Assert\Assert;
final class AnnotationToAttribute implements AnnotationToAttributeInterface
{
    /**
     * @readonly
     */
    private string $tag;
    /**
     * @readonly
     */
    private ?string $attributeClass = null;
    /**
     * @var string[]
     * @readonly
     */
    private array $classReferenceFields = [];
    /**
     * @readonly
     */
    private bool $useValueAsAttributeArgument = \false;
    /**
     * @param string[] $classReferenceFields
     */
    public function __construct(string $tag, ?string $attributeClass = null, array $classReferenceFields = [], bool $useValueAsAttributeArgument = \false)
    {
        $this->tag = $tag;
        $this->attributeClass = $attributeClass;
        $this->classReferenceFields = $classReferenceFields;
        $this->useValueAsAttributeArgument = $useValueAsAttributeArgument;
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
    public function getUseValueAsAttributeArgument() : bool
    {
        return $this->useValueAsAttributeArgument;
    }
}
