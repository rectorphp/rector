<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Rector\NodeAnalyzer\ClassLikeAnalyzer;

/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/9373a8e9f551516bc8e42aedeacd1b4f635d27fc/lib/PhpParser/NameContext.php.
 */
final class TypeContext
{
    /**
     * @var string[][]
     */
    private $variableTypes = [];

    /**
     * @var string[][]
     */
    private $propertyTypes = [];

    /**
     * @var ClassLikeAnalyzer
     */
    private $classLikeAnalyzer;

    public function __construct(
        ClassLikeAnalyzer $classLikeAnalyzer
    ) {
        $this->classLikeAnalyzer = $classLikeAnalyzer;
    }

    /**
     * @param string[] $variableTypes
     */
    public function addVariableWithTypes(string $variableName, array $variableTypes): void
    {
        $this->variableTypes[$variableName] = $variableTypes;
    }

    /**
     * @return string[]
     */
    public function getTypesForVariable(string $name): array
    {
        return $this->variableTypes[$name] ?? [];
    }

    /**
     * @return string[]
     */
    public function getTypesForProperty(string $name): array
    {
        return $this->propertyTypes[$name] ?? [];
    }

    public function addAssign(string $newVariable, string $oldVariable): void
    {
        $this->addVariableWithTypes($newVariable, $this->getTypesForVariable($oldVariable));
    }

    /**
     * @param string[] $propertyTypes
     */
    public function addPropertyTypes(string $propertyName, array $propertyTypes): void
    {
        $this->propertyTypes[$propertyName] = $propertyTypes;
    }
}
