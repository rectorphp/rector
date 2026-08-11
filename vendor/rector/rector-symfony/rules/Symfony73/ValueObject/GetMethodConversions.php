<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\ValueObject;

use PhpParser\Node\Expr\Array_;
final class GetMethodConversions
{
    /**
     * @readonly
     */
    private string $methodName;
    /**
     * @readonly
     */
    private string $attributeClass;
    /**
     * @readonly
     */
    private Array_ $returnArray;
    /**
     * @var AsTwigAttributeConversion[]
     * @readonly
     */
    private array $asTwigAttributeConversions;
    /**
     * @param AsTwigAttributeConversion[] $asTwigAttributeConversions
     */
    public function __construct(string $methodName, string $attributeClass, Array_ $returnArray, array $asTwigAttributeConversions)
    {
        $this->methodName = $methodName;
        $this->attributeClass = $attributeClass;
        $this->returnArray = $returnArray;
        $this->asTwigAttributeConversions = $asTwigAttributeConversions;
    }
    public function getMethodName(): string
    {
        return $this->methodName;
    }
    public function getAttributeClass(): string
    {
        return $this->attributeClass;
    }
    public function getReturnArray(): Array_
    {
        return $this->returnArray;
    }
    /**
     * @return AsTwigAttributeConversion[]
     */
    public function getAsTwigAttributeConversions(): array
    {
        return $this->asTwigAttributeConversions;
    }
}
