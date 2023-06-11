<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use Rector\Core\Validation\RectorAssert;
final class AnnotationPropertyToAttributeClass
{
    /**
     * @readonly
     * @var string
     */
    private $attributeClass;
    /**
     * @readonly
     * @var string|int|null
     */
    private $annotationProperty = null;
    /**
     * @readonly
     * @var bool
     */
    private $doesNeedNewImport = \false;
    /**
     * @param string|int|null $annotationProperty
     */
    public function __construct(string $attributeClass, $annotationProperty = null, bool $doesNeedNewImport = \false)
    {
        $this->attributeClass = $attributeClass;
        $this->annotationProperty = $annotationProperty;
        $this->doesNeedNewImport = $doesNeedNewImport;
        RectorAssert::className($attributeClass);
    }
    /**
     * @return string|int|null
     */
    public function getAnnotationProperty()
    {
        return $this->annotationProperty;
    }
    public function getAttributeClass() : string
    {
        return $this->attributeClass;
    }
    public function doesNeedNewImport() : bool
    {
        return $this->doesNeedNewImport;
    }
}
