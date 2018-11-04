<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Builder;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\TraitUse;

/**
 * @todo worth better naming and decoupling
 *
 * Various:
 * - adds method to class on first position
 * - adds trait to class
 * - ...
 */
final class StatementGlue
{
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
}
