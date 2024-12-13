<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

final class AttributeValueAndDocComment
{
    /**
     * @readonly
     */
    public string $attributeValue;
    /**
     * @readonly
     */
    public string $docComment;
    public function __construct(string $attributeValue, string $docComment)
    {
        $this->attributeValue = $attributeValue;
        $this->docComment = $docComment;
    }
}
