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
use Rector\Core\Exception\Node\MissingParentNodeException;
use Rector\Core\NodeManipulator\AssignManipulator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ReadWrite\Guard\VariableToConstantGuard;
use Webmozart\Assert\Assert;

final class ReadWritePropertyAnalyzer
{
    /**
     * @var VariableToConstantGuard
     */
    private $variableToConstantGuard;

    /**
     * @var AssignManipulator
     */
    private $assignManipulator;

    /**
     * @var ReadExprAnalyzer
     */
    private $readExprAnalyzer;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(
        VariableToConstantGuard $variableToConstantGuard,
        AssignManipulator $assignManipulator,
        ReadExprAnalyzer $readExprAnalyzer,
        BetterNodeFinder $betterNodeFinder
    ) {
        $this->variableToConstantGuard = $variableToConstantGuard;
        $this->assignManipulator = $assignManipulator;
        $this->readExprAnalyzer = $readExprAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $node
     */
    public function isRead(Node $node): bool
    {
        Assert::isAnyOf($node, [PropertyFetch::class, StaticPropertyFetch::class]);

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Node) {
            throw new MissingParentNodeException();
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
                throw new MissingParentNodeException();
            }
        }

        return $node;
    }

    private function isNotInsideIssetUnset(ArrayDimFetch $arrayDimFetch): bool
    {
        return ! (bool) $this->betterNodeFinder->findParentTypes($arrayDimFetch, [Isset_::class, Unset_::class]);
    }

    private function isArrayDimFetchRead(ArrayDimFetch $arrayDimFetch): bool
    {
        $parentParent = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentParent instanceof Node) {
            throw new MissingParentNodeException();
        }

        if (! $this->assignManipulator->isLeftPartOfAssign($arrayDimFetch)) {
            return false;
        }

        if ($arrayDimFetch->var instanceof ArrayDimFetch) {
            return true;
        }

        // the array dim fetch is assing here only; but the variable might be used later
        if ($this->readExprAnalyzer->isExprRead($arrayDimFetch->var)) {
            return true;
        }

        return ! $this->assignManipulator->isLeftPartOfAssign($arrayDimFetch);
    }
}
