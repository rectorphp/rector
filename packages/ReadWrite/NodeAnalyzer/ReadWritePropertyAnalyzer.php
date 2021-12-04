<?php

declare(strict_types=1);

namespace Rector\ReadWrite\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Unset_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\AssignManipulator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ReadWrite\Guard\VariableToConstantGuard;

final class ReadWritePropertyAnalyzer
{
    public function __construct(
        private readonly VariableToConstantGuard $variableToConstantGuard,
        private readonly AssignManipulator $assignManipulator,
        private readonly ReadExprAnalyzer $readExprAnalyzer,
        private readonly BetterNodeFinder $betterNodeFinder,
    ) {
    }

    public function isRead(PropertyFetch | StaticPropertyFetch $node): bool
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Node) {
            throw new ShouldNotHappenException();
        }

        $parent = $this->unwrapPostPreIncDec($parent);

        if ($parent instanceof Arg) {
            $readArg = $this->variableToConstantGuard->isReadArg($parent);
            if ($readArg) {
                return true;
            }
        }

        if ($parent instanceof ArrayDimFetch && $parent->dim === $node && $this->isNotInsideIssetUnset($parent)) {
            return $this->isArrayDimFetchRead($parent);
        }

        return ! $this->assignManipulator->isLeftPartOfAssign($node);
    }

    private function unwrapPostPreIncDec(Node $node): Node
    {
        if ($node instanceof PreInc || $node instanceof PreDec || $node instanceof PostInc || $node instanceof PostDec) {
            $node = $node->getAttribute(AttributeKey::PARENT_NODE);
            if (! $node instanceof Node) {
                throw new ShouldNotHappenException();
            }
        }

        return $node;
    }

    private function isNotInsideIssetUnset(ArrayDimFetch $arrayDimFetch): bool
    {
        return ! (bool) $this->betterNodeFinder->findParentByTypes($arrayDimFetch, [Isset_::class, Unset_::class]);
    }

    private function isArrayDimFetchRead(ArrayDimFetch $arrayDimFetch): bool
    {
        $parentParent = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentParent instanceof Node) {
            throw new ShouldNotHappenException();
        }

        if (! $this->assignManipulator->isLeftPartOfAssign($arrayDimFetch)) {
            return false;
        }

        if ($arrayDimFetch->var instanceof ArrayDimFetch) {
            return true;
        }

        // the array dim fetch is assing here only; but the variable might be used later
        return $this->readExprAnalyzer->isExprRead($arrayDimFetch->var);
    }
}
