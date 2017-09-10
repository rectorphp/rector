<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\TypesExtractor;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use ReflectionClass;

final class ConstructorPropertyTypesExtractor
{
    /**
     * @return string[] { propertyName => propertyType }
     */
    public function extractFromClassNode(Class_ $classNode): array
    {
        $constructorParametersWithTypes = $this->getConstructorParametersWithTypes($classNode);
        if (! count($constructorParametersWithTypes)) {
            return [];
        }

        foreach ($classNode->stmts as $inClassNode) {
            if (! $this->isContructorMethodNode($inClassNode)) {
                continue;
            }

            return $this->extractPropertiesFromConstructorMethodNode($inClassNode, $constructorParametersWithTypes);
        }

        return [];
    }

    /**
     * @return string[] { parameterName => parameterType }
     */
    private function getConstructorParametersWithTypes(Class_ $classNode): array
    {
        $className = $classNode->namespacedName->toString();
        if (! class_exists($className, false)) {
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

    private function isContructorMethodNode(Node $node): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        return (string) $node->name === '__construct';
    }

    private function isAssignThisNode(Node $node): bool
    {
        if (! $node instanceof Expression) {
            return false;
        }

        if ($this->isParentConstructCall($node)) {
            return false;
        }

        if (! $node->expr instanceof Assign) {
            return false;
        }

        if (! $node->expr->var instanceof PropertyFetch) {
            return false;
        }

        return $node->expr->var->var->name === 'this';
    }

    /**
     * @param string[] $constructorParametersWithTypes
     * @return string[]
     */
    private function extractPropertiesFromConstructorMethodNode(
        ClassMethod $classMethodNode,
        array $constructorParametersWithTypes
    ): array {
        $propertiesWithTypes = [];

        foreach ($classMethodNode->stmts as $inConstructorNode) {
            if (! $this->isAssignThisNode($inConstructorNode)) {
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

        return $propertiesWithTypes;
    }

    private function isParentConstructCall(Node $node): bool
    {
        if (! $node instanceof Expression) {
            return false;
        }

        if (! $node->expr instanceof StaticCall) {
            return false;
        }

        return $node->expr->name === '__construct';
    }
}
