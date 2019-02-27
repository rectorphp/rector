<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\TraitUse;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Node\VariableInfo;

final class ClassManipulator
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var ChildAndParentClassManipulator
     */
    private $childAndParentClassManipulator;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(
        NameResolver $nameResolver,
        NodeFactory $nodeFactory,
        ChildAndParentClassManipulator $childAndParentClassManipulator,
        BetterNodeFinder $betterNodeFinder
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->nameResolver = $nameResolver;
        $this->childAndParentClassManipulator = $childAndParentClassManipulator;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function addConstructorDependency(Class_ $classNode, VariableInfo $variableInfo): void
    {
        // add property
        // @todo should be factory
        $this->addPropertyToClass($classNode, $variableInfo);

        // pass via constructor
        $this->addSimplePropertyAssignToClass($classNode, $variableInfo);
    }

    /**
     * @param ClassMethod|Property|ClassMethod $stmt
     */
    public function addAsFirstMethod(Class_ $class, Stmt $stmt): void
    {
        if ($this->tryInsertBeforeFirstMethod($class, $stmt)) {
            return;
        }

        if ($this->tryInsertAfterLastProperty($class, $stmt)) {
            return;
        }

        $class->stmts[] = $stmt;
    }

    public function addAsFirstTrait(Class_ $class, Stmt $stmt): void
    {
        $this->addStatementToClassBeforeTypes($class, $stmt, TraitUse::class, Property::class);
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[] $nodes
     */
    public function insertBeforeAndFollowWithNewline(array $nodes, Stmt $stmt, int $key): array
    {
        $nodes = $this->insertBefore($nodes, $stmt, $key);
        return $this->insertBefore($nodes, new Nop(), $key);
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[] $nodes
     */
    public function insertBefore(array $nodes, Stmt $stmt, int $key): array
    {
        array_splice($nodes, $key, 0, [$stmt]);

        return $nodes;
    }

    public function addPropertyToClass(Class_ $classNode, VariableInfo $variableInfo): void
    {
        if ($this->hasClassProperty($classNode, $variableInfo->getName())) {
            return;
        }

        $propertyNode = $this->nodeFactory->createPrivatePropertyFromVariableInfo($variableInfo);
        $this->addAsFirstMethod($classNode, $propertyNode);
    }

    public function addSimplePropertyAssignToClass(Class_ $classNode, VariableInfo $variableInfo): void
    {
        $propertyAssignNode = $this->nodeFactory->createPropertyAssignment($variableInfo->getName());
        $this->addConstructorDependencyWithCustomAssign($classNode, $variableInfo, $propertyAssignNode);
    }

    public function addConstructorDependencyWithCustomAssign(
        Class_ $classNode,
        VariableInfo $variableInfo,
        Assign $assign
    ): void {
        $constructorMethod = $classNode->getMethod('__construct');
        /** @var ClassMethod $constructorMethod */
        if ($constructorMethod !== null) {
            $this->addParameterAndAssignToMethod($constructorMethod, $variableInfo, $assign);
            return;
        }

        $constructorMethod = $this->nodeFactory->createPublicMethod('__construct');

        $this->childAndParentClassManipulator->completeParentConstructor($classNode, $constructorMethod);

        $this->addParameterAndAssignToMethod($constructorMethod, $variableInfo, $assign);

        $this->addAsFirstMethod($classNode, $constructorMethod);

        $this->childAndParentClassManipulator->completeChildConstructors($classNode, $constructorMethod);
    }

    /**
     * @return ClassMethod[]
     */
    public function getMethodsByName(Class_ $classNode): array
    {
        $methodsByName = [];
        foreach ($classNode->stmts as $stmt) {
            if ($stmt instanceof ClassMethod) {
                $methodsByName[(string) $stmt->name] = $stmt;
            }
        }

        return $methodsByName;
    }

    /**
     * @return string[]
     */
    public function getUsedTraits(Class_ $classNode): array
    {
        $usedTraits = [];
        foreach ($classNode->stmts as $stmt) {
            if (! $stmt instanceof TraitUse) {
                continue;
            }

            foreach ($stmt->traits as $trait) {
                $traitName = $this->nameResolver->resolve($trait);
                if ($traitName !== null) {
                    $usedTraits[] = $traitName;
                }
            }
        }

        return $usedTraits;
    }

    public function getProperty(Class_ $class, string $name): ?PropertyProperty
    {
        foreach ($class->stmts as $stmt) {
            if (! $stmt instanceof Property) {
                continue;
            }

            foreach ($stmt->props as $propertyProperty) {
                if ($this->nameResolver->isName($propertyProperty, $name)) {
                    return $propertyProperty;
                }
            }
        }

        return null;
    }

    public function hasParentMethodOrInterface(string $class, string $method): bool
    {
        if (! class_exists($class)) {
            return false;
        }

        $parentClass = $class;
        while ($parentClass = get_parent_class($parentClass)) {
            if (method_exists($parentClass, $method)) {
                return true;
            }
        }

        $implementedInterfaces = class_implements($class);
        foreach ($implementedInterfaces as $implementedInterface) {
            if (method_exists($implementedInterface, $method)) {
                return true;
            }
        }

        return false;
    }

    public function hasClassMethod(Class_ $classNode, string $methodName): bool
    {
        $methodNames = $this->getClassMethodNames($classNode);

        return in_array($methodName, $methodNames, true);
    }

    public function getMethod(Class_ $classNode, string $methodName): ?ClassMethod
    {
        foreach ($classNode->stmts as $stmt) {
            if (! $stmt instanceof ClassMethod) {
                continue;
            }

            if ($this->nameResolver->isName($stmt, $methodName)) {
                return $stmt;
            }
        }

        return null;
    }

    private function tryInsertBeforeFirstMethod(Class_ $classNode, Stmt $stmt): bool
    {
        foreach ($classNode->stmts as $key => $classElementNode) {
            if ($classElementNode instanceof ClassMethod) {
                $classNode->stmts = $this->insertBefore($classNode->stmts, $stmt, $key);

                return true;
            }
        }

        return false;
    }

    private function tryInsertAfterLastProperty(Class_ $classNode, Stmt $stmt): bool
    {
        $previousElement = null;
        foreach ($classNode->stmts as $key => $classElementNode) {
            if ($previousElement instanceof Property && ! $classElementNode instanceof Property) {
                $classNode->stmts = $this->insertBefore($classNode->stmts, $stmt, $key);

                return true;
            }

            $previousElement = $classElementNode;
        }

        return false;
    }

    /**
     * @param string[] ...$types
     */
    private function addStatementToClassBeforeTypes(Class_ $classNode, Stmt $stmt, string ...$types): void
    {
        foreach ($types as $type) {
            foreach ($classNode->stmts as $key => $classElementNode) {
                if ($classElementNode instanceof $type) {
                    $classNode->stmts = $this->insertBefore($classNode->stmts, $stmt, $key);

                    return;
                }
            }
        }

        $classNode->stmts[] = $stmt;
    }

    private function hasClassProperty(Class_ $classNode, string $name): bool
    {
        foreach ($classNode->stmts as $inClassNode) {
            if (! $inClassNode instanceof Property) {
                continue;
            }

            if ($this->nameResolver->isName($inClassNode, $name)) {
                return true;
            }
        }

        return false;
    }

    private function addParameterAndAssignToMethod(
        ClassMethod $classMethod,
        VariableInfo $variableInfo,
        Assign $assign
    ): void {
        if ($this->hasMethodParameter($classMethod, $variableInfo)) {
            return;
        }

        $classMethod->params[] = $this->nodeFactory->createParamFromVariableInfo($variableInfo);
        $classMethod->stmts[] = new Expression($assign);
    }

    /**
     * @return string[]
     */
    private function getClassMethodNames(Class_ $classNode): array
    {
        $classMethodNames = [];

        $classMethodNodes = $this->betterNodeFinder->findInstanceOf($classNode->stmts, ClassMethod::class);
        foreach ($classMethodNodes as $classMethodNode) {
            $classMethodNames[] = $this->nameResolver->resolve($classMethodNode);
        }

        return $classMethodNames;
    }

    private function hasMethodParameter(ClassMethod $classMethod, VariableInfo $variableInfo): bool
    {
        foreach ($classMethod->params as $constructorParameter) {
            if ($this->nameResolver->isName($constructorParameter->var, $variableInfo->getName())) {
                return true;
            }
        }

        return false;
    }
}
