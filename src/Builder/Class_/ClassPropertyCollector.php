<?php declare(strict_types=1);

namespace Rector\Builder\Class_;

final class ClassPropertyCollector
{
    /**
     * @var VariableInfo[][]
     */
    private $classProperties = [];

    /**
     * @var VariableInfoFactory
     */
    private $variableInfoFactory;

    public function __construct(VariableInfoFactory $variableInfoFactory)
    {
        $this->variableInfoFactory = $variableInfoFactory;
    }

    public function addPropertyForClass(string $class, string $propertyType, string $propertyName): void
    {
        $this->classProperties[$class][] = $this->variableInfoFactory->createFromNameAndTypes(
            $propertyName,
            [$propertyType]
        );
    }

    /**
     * @return VariableInfo[]
     */
    public function getPropertiesForClass(string $class): array
    {
        return $this->classProperties[$class] ?? [];
    }
}
