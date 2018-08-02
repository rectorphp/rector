<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use Rector\NodeAnalyzer\ClassLikeAnalyzer;
use Rector\NodeTypeResolver\TypesExtractor\ConstructorPropertyTypesExtractor;

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
     * @var ConstructorPropertyTypesExtractor
     */
    private $constructorPropertyTypesExtractor;

    /**
     * @var ClassLikeAnalyzer
     */
    private $classLikeAnalyzer;

    public function __construct(
        ConstructorPropertyTypesExtractor $constructorPropertyTypesExtractor,
        ClassLikeAnalyzer $classLikeAnalyzer
    ) {
        $this->constructorPropertyTypesExtractor = $constructorPropertyTypesExtractor;
        $this->classLikeAnalyzer = $classLikeAnalyzer;
    }

    /**
     * @param string[] $variableTypes
     */
    public function addVariableWithTypes(string $variableName, array $variableTypes): void
    {
        $this->variableTypes[$variableName] = $variableTypes;
    }

    public function enterClassLike(ClassLike $classLikeNode): void
    {
        $this->propertyTypes = [];

        if ($this->classLikeAnalyzer->isNormalClass($classLikeNode)) {
            /** @var Class_ $classLikeNode */
            $this->propertyTypes = $this->constructorPropertyTypesExtractor->extractFromClassNode($classLikeNode);
        }
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
