<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\ValueObject;

final class VariableNameToType
{
    /**
     * @readonly
     */
    private string $variableName;
    /**
     * @readonly
     */
    private string $objectType;
    public function __construct(string $variableName, string $objectType)
    {
        $this->variableName = $variableName;
        $this->objectType = $objectType;
    }
    public function getVariableName() : string
    {
        return $this->variableName;
    }
    public function getObjectType() : string
    {
        return $this->objectType;
    }
}
