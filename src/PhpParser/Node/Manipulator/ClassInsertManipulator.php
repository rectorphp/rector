<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\TraitUse;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;

final class ClassInsertManipulator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeNameResolver $nodeNameResolver, NodeFactory $nodeFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * @param ClassMethod|Property|ClassConst|ClassMethod $stmt
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

    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function insertBeforeAndFollowWithNewline(array $nodes, Stmt $stmt, int $key): array
    {
        $nodes = $this->insertBefore($nodes, $stmt, $key);

        return $this->insertBefore($nodes, new Nop(), $key);
    }

    public function addConstantToClass(Class_ $class, string $constantName, ClassConst $classConst): void
    {
        if ($this->hasClassConstant($class, $constantName)) {
            return;
        }

        $this->addAsFirstMethod($class, $classConst);
    }

    public function addPropertyToClass(Class_ $class, string $name, ?Type $type): void
    {
        if ($this->hasClassProperty($class, $name)) {
            return;
        }

        $propertyNode = $this->nodeFactory->createPrivatePropertyFromNameAndType($name, $type);
        $this->addAsFirstMethod($class, $propertyNode);
    }

    public function addAsFirstTrait(Class_ $class, string $traitName): void
    {
        $trait = new TraitUse([new FullyQualified($traitName)]);

        $this->addStatementToClassBeforeTypes($class, $trait, TraitUse::class, Property::class, ClassMethod::class);
    }

    private function tryInsertBeforeFirstMethod(Class_ $classNode, Stmt $stmt): bool
    {
        foreach ($classNode->stmts as $key => $classStmt) {
            if (! $classStmt instanceof ClassMethod) {
                continue;
            }

            $classNode->stmts = $this->insertBefore($classNode->stmts, $stmt, $key);
            return true;
        }

        return false;
    }

    private function tryInsertAfterLastProperty(Class_ $classNode, Stmt $stmt): bool
    {
        $previousElement = null;
        foreach ($classNode->stmts as $key => $classStmt) {
            if ($previousElement instanceof Property && ! $classStmt instanceof Property) {
                $classNode->stmts = $this->insertBefore($classNode->stmts, $stmt, $key);

                return true;
            }

            $previousElement = $classStmt;
        }

        return false;
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    private function insertBefore(array $nodes, Stmt $stmt, int $key): array
    {
        array_splice($nodes, $key, 0, [$stmt]);

        return $nodes;
    }

    private function hasClassConstant(Class_ $class, string $constantName)
    {
        foreach ($class->getConstants() as $classConst) {
            if ($this->nodeNameResolver->isName($classConst, $constantName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Waits on https://github.com/nikic/PHP-Parser/pull/646
     */
    private function hasClassProperty(Class_ $classNode, string $name): bool
    {
        foreach ($classNode->getProperties() as $property) {
            if (! $this->nodeNameResolver->isName($property, $name)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @param string[] ...$types
     */
    private function addStatementToClassBeforeTypes(Class_ $class, Stmt $stmt, string ...$types): void
    {
        foreach ($types as $type) {
            foreach ($class->stmts as $key => $classStmt) {
                if (! $classStmt instanceof $type) {
                    continue;
                }
                $class->stmts = $this->insertBefore($class->stmts, $stmt, $key);

                return;
            }
        }

        $class->stmts[] = $stmt;
    }
}
