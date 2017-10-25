<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\BetterReflection\Reflection\ReflectionFunction;
use Rector\BetterReflection\Reflection\ReflectionMethod;
use Rector\BetterReflection\Reflector\MethodReflector;
use Rector\NodeAnalyzer\ClassAnalyzer;
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
     * @var ClassLike|null
     */
    private $classLikeNode;

    /**
     * @var ConstructorPropertyTypesExtractor
     */
    private $constructorPropertyTypesExtractor;

    /**
     * @var MethodReflector
     */
    private $methodReflector;

    /**
     * @var ClassAnalyzer
     */
    private $classAnalyzer;

    public function __construct(
        ConstructorPropertyTypesExtractor $constructorPropertyTypesExtractor,
        MethodReflector $methodReflector,
        ClassAnalyzer $classAnalyzer
    ) {
        $this->constructorPropertyTypesExtractor = $constructorPropertyTypesExtractor;
        $this->methodReflector = $methodReflector;
        $this->classAnalyzer = $classAnalyzer;
    }

    public function startFile(): void
    {
        $this->variableTypes = [];
        $this->propertyTypes = [];
        $this->classLikeNode = null;
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
        $this->classLikeNode = $classLikeNode;

        if ($this->classAnalyzer->isNormalClass($classLikeNode)) {
            /** @var Class_ $classLikeNode */
            $this->propertyTypes = $this->constructorPropertyTypesExtractor->extractFromClassNode($classLikeNode);
        }
    }

    public function enterFunction(FunctionLike $functionLikeNode): void
    {
        $this->variableTypes = [];

        $functionReflection = $this->getFunctionReflection($functionLikeNode);
        if ($functionReflection) {
            foreach ($functionReflection->getParameters() as $parameterReflection) {
                $this->variableTypes[$parameterReflection->getName()] = (string) $parameterReflection->getType();
            }
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
        $variableTypes = $this->getTypesForVariable($oldVariable);
        $this->addVariableWithTypes($newVariable, $variableTypes);
    }

    /**
     * @param string[] $propertyTypes
     */
    public function addPropertyTypes(string $propertyName, array $propertyTypes): void
    {
        $this->propertyTypes[$propertyName] = $propertyTypes;
    }

    /**
     * @param Function_|ClassMethod|Closure $functionLikeNode
     *
     * @return ReflectionFunction|ReflectionMethod|null
     */
    private function getFunctionReflection(FunctionLike $functionLikeNode)
    {
        if ($functionLikeNode instanceof Closure) {
            return null;
        }

        if ($this->classLikeNode) {
            if ($this->classAnalyzer->isAnonymousClassNode($this->classLikeNode)) {
                return null;
            }

            $className = $this->classLikeNode->namespacedName->toString();

            $methodName = (string) $functionLikeNode->name;

            return $this->methodReflector->reflectClassMethod($className, $methodName);
        }

        /** @var Function_ $functionLikeNode */
        $functionName = (string) $functionLikeNode->name;
        if (! function_exists($functionName)) {
            return null;
        }

        return ReflectionFunction::createFromName($functionName);
    }
}
