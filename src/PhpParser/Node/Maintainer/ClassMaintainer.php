<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Maintainer;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\TraitUse;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Node\VariableInfo;
use function Safe\class_implements;

final class ClassMaintainer
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
     * @var ChildAndParentClassMaintainer
     */
    private $childAndParentClassMaintainer;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(
        NameResolver $nameResolver,
        NodeFactory $nodeFactory,
        ChildAndParentClassMaintainer $childAndParentClassMaintainer,
        BetterNodeFinder $betterNodeFinder
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->nameResolver = $nameResolver;
        $this->childAndParentClassMaintainer = $childAndParentClassMaintainer;
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
     * @param ClassMethod|Property|ClassMethod $node
     */
    public function addAsFirstMethod(Class_ $classNode, Stmt $node): void
    {
        if ($this->tryInsertBeforeFirstMethod($classNode, $node)) {
            return;
        }

        if ($this->tryInsertAfterLastProperty($classNode, $node)) {
            return;
        }

        $classNode->stmts[] = $node;
    }

    public function addAsFirstTrait(Class_ $classNode, Stmt $node): void
    {
        $this->addStatementToClassBeforeTypes($classNode, $node, TraitUse::class, Property::class);
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[] $nodes
     */
    public function insertBeforeAndFollowWithNewline(array $nodes, Stmt $node, int $key): array
    {
        $nodes = $this->insertBefore($nodes, $node, $key);
        return $this->insertBefore($nodes, new Nop(), $key);
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[] $nodes
     */
    public function insertBefore(array $nodes, Stmt $node, int $key): array
    {
        array_splice($nodes, $key, 0, [$node]);

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
        Assign $assignNode
    ): void {
        $constructorMethod = $classNode->getMethod('__construct');
        /** @var ClassMethod $constructorMethod */
        if ($constructorMethod) {
            $this->addParameterAndAssignToMethod($constructorMethod, $variableInfo, $assignNode);
            return;
        }

        $constructorMethod = $this->nodeFactory->createPublicMethod('__construct');

        $this->childAndParentClassMaintainer->completeParentConstructor($classNode, $constructorMethod);

        $this->addParameterAndAssignToMethod($constructorMethod, $variableInfo, $assignNode);

        $this->addAsFirstMethod($classNode, $constructorMethod);

        $this->childAndParentClassMaintainer->completeChildConstructors($classNode, $constructorMethod);
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

    public function getProperty(Class_ $class, string $name): ?Property
    {
        foreach ($class->stmts as $stmt) {
            if (! $stmt instanceof Property) {
                continue;
            }

            if ($this->nameResolver->isName($stmt->props[0], $name)) {
                return $stmt;
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

    private function tryInsertBeforeFirstMethod(Class_ $classNode, Stmt $node): bool
    {
        foreach ($classNode->stmts as $key => $classElementNode) {
            if ($classElementNode instanceof ClassMethod) {
                $classNode->stmts = $this->insertBefore($classNode->stmts, $node, $key);

                return true;
            }
        }

        return false;
    }

    private function tryInsertAfterLastProperty(Class_ $classNode, Stmt $node): bool
    {
        $previousElement = null;
        foreach ($classNode->stmts as $key => $classElementNode) {
            if ($previousElement instanceof Property && ! $classElementNode instanceof Property) {
                $classNode->stmts = $this->insertBefore($classNode->stmts, $node, $key);

                return true;
            }

            $previousElement = $classElementNode;
        }

        return false;
    }

    /**
     * @param string[] ...$types
     */
    private function addStatementToClassBeforeTypes(Class_ $classNode, Stmt $node, string ...$types): void
    {
        foreach ($types as $type) {
            foreach ($classNode->stmts as $key => $classElementNode) {
                if ($classElementNode instanceof $type) {
                    $classNode->stmts = $this->insertBefore($classNode->stmts, $node, $key);

                    return;
                }
            }
        }

        $classNode->stmts[] = $node;
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
        ClassMethod $classMethodNode,
        VariableInfo $variableInfo,
        Assign $propertyAssignNode
    ): void {
        if ($this->hasMethodParameter($classMethodNode, $variableInfo)) {
            return;
        }

        $classMethodNode->params[] = $this->nodeFactory->createParamFromVariableInfo($variableInfo);
        $classMethodNode->stmts[] = new Expression($propertyAssignNode);
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

    private function hasMethodParameter(ClassMethod $classMethodNode, VariableInfo $variableInfo): bool
    {
        foreach ($classMethodNode->params as $constructorParameter) {
            if ($this->nameResolver->isName($constructorParameter->var, $variableInfo->getName())) {
                return true;
            }
        }

        return false;
    }
}
