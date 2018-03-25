<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\BetterReflection\Reflection\ReflectionFunction;
use Rector\BetterReflection\Reflection\ReflectionFunctionAbstract;
use Rector\BetterReflection\Reflection\ReflectionMethod;
use Rector\BetterReflection\Reflector\MethodReflector;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\ClassLikeAnalyzer;
use Rector\NodeTypeResolver\TypesExtractor\ConstructorPropertyTypesExtractor;
use Throwable;

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
     * @var ClassLikeAnalyzer
     */
    private $classLikeAnalyzer;

    public function __construct(
        ConstructorPropertyTypesExtractor $constructorPropertyTypesExtractor,
        MethodReflector $methodReflector,
        ClassLikeAnalyzer $classLikeAnalyzer
    ) {
        $this->constructorPropertyTypesExtractor = $constructorPropertyTypesExtractor;
        $this->methodReflector = $methodReflector;
        $this->classLikeAnalyzer = $classLikeAnalyzer;
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

    public function enterClassLike(Class_ $classLikeNode): void
    {
        $this->propertyTypes = [];
        $this->classLikeNode = $classLikeNode;

        if ($this->classLikeAnalyzer->isNormalClass($classLikeNode)) {
            $this->propertyTypes = $this->constructorPropertyTypesExtractor->extractFromClassNode($classLikeNode);
        }
    }

    public function enterFunction(FunctionLike $functionLikeNode): void
    {
        $this->variableTypes = [];

        try {
            $functionReflection = $this->getFunctionReflection($functionLikeNode);
            if ($functionReflection) {
                $this->processFunctionVariableTypes($functionReflection);
            }
        } catch (Throwable $throwable) {
            // function not autoloaded
            return;
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
            if ($this->classLikeAnalyzer->isAnonymousClassNode($this->classLikeNode)) {
                return null;
            }

            $className = $this->classLikeNode->namespacedName->toString();
            $methodName = $functionLikeNode->name;
            return $this->methodReflector->reflectClassMethod($className, $methodName);
        }

        /** @var Function_ $functionLikeNode */
        $functionName = $functionLikeNode->name;

        $namespacedFunctionName = $this->prefixFunctionWithNamespace($functionLikeNode, $functionName);
        if (function_exists($namespacedFunctionName)) {
            return ReflectionFunction::createFromName($namespacedFunctionName);
        }

        // PHP native function
        return ReflectionFunction::createFromName($functionName);
    }

    private function processFunctionVariableTypes(ReflectionFunctionAbstract $reflectionFunctionAbstract): void
    {
        foreach ($reflectionFunctionAbstract->getParameters() as $parameterReflection) {
            $type = (string) $parameterReflection->getType();
            if (! $type) {
                continue;
            }

            $this->variableTypes[$parameterReflection->getName()] = [$type];
        }
    }

    private function prefixFunctionWithNamespace(Node $node, string $functionName): string
    {
        /** @var string $namespaceName */
        $namespaceName = $node->getAttribute(Attribute::NAMESPACE_NAME);

        return $namespaceName . '\\' . $functionName;
    }
}
