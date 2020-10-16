<?php

declare(strict_types=1);

namespace Rector\ReadWrite\ReadNodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use Rector\Core\Exception\Node\MissingParentNodeException;
use Rector\Core\PhpParser\Node\Manipulator\AssignManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ReadWrite\Contract\ReadExprAnalyzerAwareInterface;
use Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use Rector\ReadWrite\NodeAnalyzer\ReadExprAnalyzer;
use Rector\SOLID\Guard\VariableToConstantGuard;

final class PropertyFetchReadNodeAnalyzer extends AbstractReadNodeAnalyzer implements ReadNodeAnalyzerInterface, ReadExprAnalyzerAwareInterface
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

    public function __construct(
        VariableToConstantGuard $variableToConstantGuard,
        AssignManipulator $assignManipulator
    ) {
        $this->variableToConstantGuard = $variableToConstantGuard;
        $this->assignManipulator = $assignManipulator;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof PropertyFetch || $node instanceof StaticPropertyFetch;
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $node
     */
    public function isRead(Node $node): bool
    {
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

        if ($parent instanceof ArrayDimFetch && $parent->dim === $node) {
            return $this->isArrayDimFetchRead($parent);
        }

        return ! $this->assignManipulator->isNodeLeftPartOfAssign($node);

        $propertyFetchUsages = $this->nodeUsageFinder->findPropertyFetchUsages($node);
        foreach ($propertyFetchUsages as $propertyFetchUsage) {
            if ($this->isCurrentContextRead($propertyFetchUsage)) {
                return true;
            }
        }
//
//        return false;
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

    /**
     * @todo decoupel to own collected type
     */
    private function isArrayDimFetchRead(ArrayDimFetch $arrayDimFetch): bool
    {
        $parentParent = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentParent === null) {
            throw new MissingParentNodeException();
        }

        if (! $this->assignManipulator->isNodeLeftPartOfAssign($arrayDimFetch)) {
            return false;
        }

        // the array dim fetch is assign here only; but the variable might be used later
        if ($this->readExprAnalyzer->isExprRead($arrayDimFetch->var)) {
            return true;
        }

        return ! $this->assignManipulator->isNodeLeftPartOfAssign($arrayDimFetch);
    }

    public function setReadExprAnalyzer(ReadExprAnalyzer $readExprAnalyzer): void
    {
        $this->readExprAnalyzer = $readExprAnalyzer;
    }
}
