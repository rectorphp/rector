<?php

declare (strict_types=1);
namespace Rector\PHPUnit\AnnotationsToAttributes\ValueObject;

use Rector\PHPUnit\Enum\PHPUnitAttribute;
final class RequiresAttributeAndValue
{
    /**
     * @var PHPUnitAttribute::*
     * @readonly
     */
    private string $attributeClass;
    /**
     * @var string[]
     * @readonly
     */
    private array $value;
    /**
     * @param PHPUnitAttribute::* $attributeClass
     * @param string[] $value
     */
    public function __construct(string $attributeClass, array $value)
    {
        $this->attributeClass = $attributeClass;
        $this->value = $value;
    }
    /**
     * @return PHPUnitAttribute::*
     */
    public function getAttributeClass(): string
    {
        return $this->attributeClass;
    }
    /**
     * @return string[]
     */
    public function getValue(): array
    {
        return $this->value;
    }
}
