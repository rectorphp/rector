<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DependencyInjection\Collector;

use RectorPrefix20220606\PHPStan\Type\ObjectType;
final class VariablesToPropertyFetchCollection
{
    /**
     * @var array<string, ObjectType>
     */
    private $variableNameAndType = [];
    public function addVariableNameAndType(string $name, ObjectType $objectType) : void
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
