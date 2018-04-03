<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\TypesExtractor;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\BetterReflection\Reflection\TypeAnalyzer;
use Rector\BetterReflection\Reflector\MethodReflector;

final class ConstructorPropertyTypesExtractor
{
    /**
     * @var MethodReflector
     */
    private $methodReflector;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    public function __construct(MethodReflector $methodReflector, TypeAnalyzer $typeAnalyzer)
    {
        $this->methodReflector = $methodReflector;
        $this->typeAnalyzer = $typeAnalyzer;
    }

    /**
     * @return string[][] { propertyName => propertyType }
     */
    public function extractFromClassNode(Class_ $classNode): array
    {
        $constructorParametersWithTypes = $this->getConstructorParametersWithTypes($classNode);
        if (count($constructorParametersWithTypes) === 0) {
            return [];
        }

        foreach ($classNode->stmts as $inClassNode) {
            if (! $this->isConstructorMethodNode($inClassNode)) {
                continue;
            }

            /** @var ClassMethod $inClassNode */
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

        $constructorMethodReflection = $this->methodReflector->reflectClassMethod($className, '__construct');
        if ($constructorMethodReflection === null) {
            return [];
        }

        $parametersWithTypes = [];

        foreach ($constructorMethodReflection->getParameters() as $parameterReflection) {
            $parameterName = $parameterReflection->getName();

            $parameterType = (string) $parameterReflection->getType();

            if ($this->typeAnalyzer->isBuiltinType($parameterType)) {
                continue;
            }

            $parametersWithTypes[$parameterName] = [$parameterType];
        }

        return $parametersWithTypes;
    }

    private function isConstructorMethodNode(Node $node): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        /** @var Identifier $identifierNode */
        $identifierNode = $node->name;

        return $identifierNode->toString() === '__construct';
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

        return $this->isThisPropertyFetch($node->expr);
    }

    /**
     * @param string[] $constructorParametersWithTypes
     * @return string[][]
     */
    private function extractPropertiesFromConstructorMethodNode(
        ClassMethod $classMethodNode,
        array $constructorParametersWithTypes
    ): array {
        $propertiesWithTypes = [];

        foreach ((array) $classMethodNode->stmts as $inConstructorNode) {
            if (! $this->isAssignThisNode($inConstructorNode)) {
                continue;
            }

            /** @var Expression $inConstructorNode */
            /** @var Assign $assignNode */
            $assignNode = $inConstructorNode->expr;

            /** @var PropertyFetch $propertyFetchNode */
            $propertyFetchNode = $assignNode->var;

            /** @var Identifier $identifierNode */
            $identifierNode = $propertyFetchNode->name;

            $propertyName = $identifierNode->toString();
            $propertyTypes = $constructorParametersWithTypes[$propertyName] ?? null;

            if ($propertyName && $propertyTypes !== null) {
                $propertiesWithTypes[$propertyName] = $propertyTypes;
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

    private function isThisPropertyFetch(Assign $assigNode): bool
    {
        if (! $assigNode->var instanceof PropertyFetch) {
            return false;
        }

        $propertyFetchNode = $assigNode->var;
        if (! $propertyFetchNode->var instanceof Variable) {
            return false;
        }

        return $propertyFetchNode->var->name === 'this';
    }
}
