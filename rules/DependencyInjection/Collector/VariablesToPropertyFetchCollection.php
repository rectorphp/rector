<?php

declare (strict_types=1);
namespace Rector\DependencyInjection\Collector;

use PHPStan\Type\ObjectType;
final class VariablesToPropertyFetchCollection
{
    /**
     * @var array<string, ObjectType>
     */
    private $variableNameAndType = [];
    public function addVariableNameAndType(string $name, \PHPStan\Type\ObjectType $objectType) : void
    {
        $this->variableNameAndType[$name] = $objectType;
    }
    /**
     * @return array<string, ObjectType>
     */
    public function getVariableNamesAndTypes() : array
    {
        return $this->variableNameAndType;
    }
}
