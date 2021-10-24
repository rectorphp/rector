<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class AttributeKeyToClassConstFetch
{
    /**
     * @param array<string, string> $valuesToConstantsMap
     */
    public function __construct(
        private string $attributeClass,
        private string $attributeKey,
        private string $constantClass,
        private array $valuesToConstantsMap
    ) {
    }

    public function getAttributeClass(): string
    {
        return $this->attributeClass;
    }

    public function getAttributeKey(): string
    {
        return $this->attributeKey;
    }

    public function getConstantClass(): string
    {
        return $this->constantClass;
    }

    /**
     * @return array<string, string>
     */
    public function getValuesToConstantsMap(): array
    {
        return $this->valuesToConstantsMap;
    }
}
