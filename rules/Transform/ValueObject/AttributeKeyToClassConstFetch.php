<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

final class AttributeKeyToClassConstFetch
{
    /**
     * @var string
     */
    private $attributeClass;
    /**
     * @var string
     */
    private $attributeKey;
    /**
     * @var string
     */
    private $constantClass;
    /**
     * @var string[]
     */
    private $valuesToConstantsMap;
    /**
     * @param array<mixed, string> $valuesToConstantsMap
     */
    public function __construct(string $attributeClass, string $attributeKey, string $constantClass, array $valuesToConstantsMap)
    {
        $this->attributeClass = $attributeClass;
        $this->attributeKey = $attributeKey;
        $this->constantClass = $constantClass;
        $this->valuesToConstantsMap = $valuesToConstantsMap;
    }
    public function getAttributeClass() : string
    {
        return $this->attributeClass;
    }
    public function getAttributeKey() : string
    {
        return $this->attributeKey;
    }
    public function getConstantClass() : string
    {
        return $this->constantClass;
    }
    /**
     * @return array<string, string>
     */
    public function getValuesToConstantsMap() : array
    {
        return $this->valuesToConstantsMap;
    }
}
