<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/9373a8e9f551516bc8e42aedeacd1b4f635d27fc/lib/PhpParser/NameContext.php.
 */
final class TypeContext
{
    /**
     * @var mixed[]
     */
    private $types = [];

    /**
     * @var string[]
     */
    private $classProperties = [];

    /**
     * @var ClassLike|nnull
     */
    private $classLikeNode;

    public function startFile(): void
    {
        $this->types = [];
        $this->classProperties = [];
        $this->classLikeNode = null;
    }

    public function addVariableWithType(string $variableName, string $variableType): void
    {
        $this->types[$variableName] = $variableType;
    }

    public function enterClass(ClassLike $classLikeNode): void
    {
        $this->classProperties = [];
        $this->classLikeNode = $classLikeNode;

        if ($classLikeNode instanceof Class_) {
            $this->classProperties = $this->prepareConstructorTypesFromClass($classLikeNode);
        }
    }

    public function enterFunction(FunctionLike $functionLikeNode): void
    {
        $this->types = [];

        $functionReflection = $this->getFunctionReflection($functionLikeNode);
        if ($functionReflection) {
            foreach ($functionReflection->getParameters() as $parameterReflection) {
                $this->types[$parameterReflection->getName()] = (string) $parameterReflection->getType();
            }
        }
    }

    public function getTypeForVariable(string $name): string
    {
        return $this->types[$name] ?? '';
    }

    public function getTypeForProperty(string $name): string
    {
        return $this->classProperties[$name] ?? '';
    }

    public function addAssign(string $newVariable, string $oldVariable): void
    {
        $type = $this->getTypeForVariable($oldVariable);
        $this->addVariableWithType($newVariable, $type);
    }

    /**
     * @return ReflectionFunction|ReflectionMethod|null
     */
    private function getFunctionReflection(FunctionLike $functionLikeNode)
    {
        if ($this->classLikeNode) {
            $className = $this->classLikeNode->namespacedName->toString();
            if (! class_exists($className)) {
                return null;
            }

            $methodName = (string) $functionLikeNode->name;

            return new ReflectionMethod($className, $methodName);
        }

        return new ReflectionFunction((string) $functionLikeNode->name);
    }

    /**
     * @return string[]
     */
    private function prepareConstructorTypesFromClass(Class_ $classNode): array
    {
        $constructorParametersWithTypes = $this->getConstructorParametersWithTypes($classNode);
        if (! count($constructorParametersWithTypes)) {
            return [];
        }

        $propertiesWithTypes = [];
        foreach ($classNode->stmts as $inClassNode) {
            if (! $inClassNode instanceof ClassMethod) {
                continue;
            }

            if ((string) $inClassNode->name !== '__construct') {
                continue;
            }

            foreach ($inClassNode->stmts as $inConstructorNode) {
                if (! $inConstructorNode->expr instanceof Assign) {
                    continue;
                }

                if (! $inConstructorNode->expr->var instanceof PropertyFetch) {
                    continue;
                }

                if ($inConstructorNode->expr->var->var->name !== 'this') {
                    continue;
                }

                /** @var PropertyFetch $propertyFetchNode */
                $propertyFetchNode = $inConstructorNode->expr->var;
                $propertyName = (string) $propertyFetchNode->name;
                $propertyType = $constructorParametersWithTypes[$propertyName] ?? null;

                if ($propertyName && $propertyType) {
                    $propertiesWithTypes[$propertyName] = $propertyType;
                }
            }
        }

        return $propertiesWithTypes;
    }

    /**
     * @return string[]
     */
    private function getConstructorParametersWithTypes(Class_ $classNode): array
    {
        $className = $classNode->namespacedName->toString();
        if (! class_exists($className)) {
            return [];
        }

        $constructorMethod = (new ReflectionClass($className))->getConstructor();
        $parametersWithTypes = [];

        if ($constructorMethod) {
            foreach ($constructorMethod->getParameters() as $parameterReflection) {
                $parameterName = $parameterReflection->getName();
                $parameterType = (string) $parameterReflection->getType();

                $parametersWithTypes[$parameterName] = $parameterType;
            }
        }

        return $parametersWithTypes;
    }
}
