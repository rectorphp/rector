<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use Rector\Core\Validation\RectorAssert;
use Rector\Php80\Contract\ValueObject\AnnotationToAttributeInterface;
use RectorPrefix202209\Webmozart\Assert\Assert;
final class NestedAnnotationToAttribute implements AnnotationToAttributeInterface
{
    /**
     * @readonly
     * @var string
     */
    private $tag;
    /**
     * @var array<string, string>|string[]
     * @readonly
     */
    private $annotationPropertiesToAttributeClasses;
    /**
     * @readonly
     * @var bool
     */
    private $removeOriginal = \false;
    /**
     * @param array<string, string>|string[] $annotationPropertiesToAttributeClasses
     */
    public function __construct(string $tag, array $annotationPropertiesToAttributeClasses, bool $removeOriginal = \false)
    {
        $this->tag = $tag;
        $this->annotationPropertiesToAttributeClasses = $annotationPropertiesToAttributeClasses;
        $this->removeOriginal = $removeOriginal;
        RectorAssert::className($tag);
        Assert::allString($annotationPropertiesToAttributeClasses);
    }
    public function getTag() : string
    {
        return $this->tag;
    }
    /**
     * @return array<string, string>|string[]
     */
    public function getAnnotationPropertiesToAttributeClasses() : array
    {
        return $this->annotationPropertiesToAttributeClasses;
    }
    public function getAttributeClass() : string
    {
        return $this->tag;
    }
    public function shouldRemoveOriginal() : bool
    {
        return $this->removeOriginal;
    }
    public function hasExplicitParameters() : bool
    {
        foreach (\array_keys($this->annotationPropertiesToAttributeClasses) as $itemName) {
            if (\is_string($itemName)) {
                return \true;
            }
        }
        return \false;
    }
}
