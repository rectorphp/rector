<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\ValueObject;

final class VariableNameToTypeCollection
{
    /**
     * @var VariableNameToType[]
     */
    private array $variableNameToType;
    /**
     * @param VariableNameToType[] $variableNameToType
     */
    public function __construct(array $variableNameToType)
    {
        $this->variableNameToType = $variableNameToType;
    }
    public function matchByVariableName(string $variableName) : ?\Rector\PHPUnit\CodeQuality\ValueObject\VariableNameToType
    {
        foreach ($this->variableNameToType as $variableNameToType) {
            if ($variableNameToType->getVariableName() !== $variableName) {
                continue;
            }
            return $variableNameToType;
        }
        return null;
    }
    public function remove(\Rector\PHPUnit\CodeQuality\ValueObject\VariableNameToType $matchedNullableVariableNameToType) : void
    {
        foreach ($this->variableNameToType as $key => $variableNamesToType) {
            if ($matchedNullableVariableNameToType !== $variableNamesToType) {
                continue;
            }
            unset($this->variableNameToType[$key]);
            break;
        }
    }
}
