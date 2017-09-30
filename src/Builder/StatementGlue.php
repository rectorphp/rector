<?php declare(strict_types=1);

namespace Rector\Builder;

use Nette\Utils\Arrays;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\TraitUse;

final class StatementGlue
{
    /**
     * @param ClassMethod|Property|ClassMethod $node
     */
    public function addAsFirstMethod(Class_ $classNode, Node $node): void
    {
        foreach ($classNode->stmts as $key => $classElementNode) {
            if ($classElementNode instanceof ClassMethod) {
                $this->insertBefore($classNode, $node, $key);

                return;
            }
        }

        $previousElement = null;
        foreach ($classNode->stmts as $key => $classElementNode) {
            if ($previousElement instanceof Property && ! $classElementNode instanceof Property) {
                $this->insertBefore($classNode, $node, $key);

                return;
            }

            $previousElement = $classElementNode;
        }

        $classNode->stmts[] = $node;
    }

    public function addAsFirstTrait(Class_ $classNode, Node $node): void
    {
        $this->addStatementToClassBeforeTypes($classNode, $node, TraitUse::class, Property::class);
    }

    private function addStatementToClassBeforeTypes(Class_ $classNode, Node $node, string ...$types): void
    {
        foreach ($types as $type) {
            foreach ($classNode->stmts as $key => $classElementNode) {
                if (is_a($classElementNode, $type, true)) {
                    $this->insertBefore($classNode, $node, $key);

                    return;
                }
            }
        }

        $classNode->stmts[] = $node;
    }

    /**
     * @param int|string $key
     */
    private function insertBefore(Class_ $classNode, Node $node, $key): void
    {
        Arrays::insertBefore($classNode->stmts, $key, [
            'before_' . $key => $node,
        ]);
    }
}
