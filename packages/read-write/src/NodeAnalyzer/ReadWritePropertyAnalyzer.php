<?php

declare(strict_types=1);

namespace Rector\ReadWrite\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Unset_;
use Rector\Core\Exception\Node\MissingParentNodeException;
use Rector\Core\PhpParser\Node\Manipulator\AssignManipulator;
use Rector\NodeNestingScope\NodeFinder\ScopeAwareNodeFinder;
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
     * @var ScopeAwareNodeFinder
     */
    private $scopeAwareNodeFinder;

    public function __construct(
        VariableToConstantGuard $variableToConstantGuard,
        AssignManipulator $assignManipulator,
        ReadExprAnalyzer $readExprAnalyzer,
        ScopeAwareNodeFinder $scopeAwareNodeFinder
    ) {
        $this->variableToConstantGuard = $variableToConstantGuard;
        $this->assignManipulator = $assignManipulator;
        $this->readExprAnalyzer = $readExprAnalyzer;
        $this->scopeAwareNodeFinder = $scopeAwareNodeFinder;
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $node
     */
    public function isRead(Node $node): bool
    {
        Assert::isAnyOf($node, [PropertyFetch::class, StaticPropertyFetch::class]);

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent === null) {
            throw new MissingParentNodeException();
        }

        $parent = $this->unwrapPostPreIncDec($parent);

        if ($parent instanceof Arg) {
            $readArg = $this->variableToConstantGuard->isReadArg($parent);
            if ($readArg) {
                return true;
            }
        }

        if ($parent instanceof ArrayDimFetch && $parent->dim === $node && $this->isNotInsideUnset($parent)) {
            return $this->isArrayDimFetchRead($parent);
        }

        return ! $this->assignManipulator->isLeftPartOfAssign($node);
    }

    private function isNotInsideUnset(Node $node): bool
    {
        return ! (bool) $this->scopeAwareNodeFinder->findParentType($node, [Unset_::class]);
    }

    private function unwrapPostPreIncDec(Node $node): Node
    {
        if ($node instanceof PreInc || $node instanceof PreDec || $node instanceof PostInc || $node instanceof PostDec) {
            $node = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($node === null) {
                throw new MissingParentNodeException();
            }
        }

        return $node;
    }

    private function isArrayDimFetchRead(ArrayDimFetch $arrayDimFetch): bool
    {
        $parentParent = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentParent === null) {
            throw new MissingParentNodeException();
        }

        if (! $this->assignManipulator->isLeftPartOfAssign($arrayDimFetch)) {
            return false;
        }

        // the array dim fetch is assing here only; but the variable might be used later
        if ($this->readExprAnalyzer->isExprRead($arrayDimFetch->var)) {
            return true;
        }

        return ! $this->assignManipulator->isLeftPartOfAssign($arrayDimFetch);
    }
}
