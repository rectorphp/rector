<?php

declare(strict_types=1);

namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Unset_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\AssignManipulator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\Util\StaticInstanceOf;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ForNodeAnalyzer
{
    /**
     * @var string
     */
    private const COUNT = 'count';

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var AssignManipulator
     */
    private $assignManipulator;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter,
        AssignManipulator $assignManipulator
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->assignManipulator = $assignManipulator;
    }

    /**
     * @param Expr[] $condExprs
     */
    public function isCondExprSmallerOrGreater(array $condExprs, string $keyValueName, string $countValueName): bool
    {
        // $i < $count
        if ($condExprs[0] instanceof Smaller) {
            if (! $this->nodeNameResolver->isName($condExprs[0]->left, $keyValueName)) {
                return false;
            }

            return $this->nodeNameResolver->isName($condExprs[0]->right, $countValueName);
        }

        // $i > $count
        if ($condExprs[0] instanceof Greater) {
            if (! $this->nodeNameResolver->isName($condExprs[0]->left, $countValueName)) {
                return false;
            }

            return $this->nodeNameResolver->isName($condExprs[0]->right, $keyValueName);
        }

        return false;
    }

    /**
     * @param Expr[] $loopExprs
     * $param
     */
    public function isLoopMatch(array $loopExprs, ?string $keyValueName): bool
    {
        if (count($loopExprs) !== 1) {
            return false;
        }

        if ($keyValueName === null) {
            return false;
        }

        /** @var PreInc|PostInc $prePostInc */
        $prePostInc = $loopExprs[0];
        if (StaticInstanceOf::isOneOf($prePostInc, [PreInc::class, PostInc::class])) {
            return $this->nodeNameResolver->isName($prePostInc->var, $keyValueName);
        }

        return false;
    }

    public function isCountValueVariableUsedInsideForStatements(For_ $for, ?Expr $expr): bool
    {
        return (bool) $this->betterNodeFinder->findFirst(
            $for->stmts,
            function (Node $node) use ($expr): bool {
                return $this->betterStandardPrinter->areNodesEqual($node, $expr);
            }
        );
    }

    public function isArrayWithKeyValueNameUnsetted(For_ $for): bool
    {
        return (bool) $this->betterNodeFinder->findFirst(
            $for->stmts,
            function (Node $node): bool {
                /** @var Node $parent */
                $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
                if (! $parent instanceof Unset_) {
                    return false;
                }
                return $node instanceof ArrayDimFetch;
            }
        );
    }

    public function isAssignmentWithArrayDimFetchAsVariableInsideForStatements(For_ $for, ?string $keyValueName): bool
    {
        return (bool) $this->betterNodeFinder->findFirst(
            $for->stmts,
            function (Node $node) use ($keyValueName): bool {
                if (! $node instanceof Assign) {
                    return false;
                }

                if (! $node->var instanceof ArrayDimFetch) {
                    return false;
                }

                if ($keyValueName === null) {
                    throw new ShouldNotHappenException();
                }

                $arrayDimFetch = $node->var;
                if ($arrayDimFetch->dim === null) {
                    return false;
                }

                if (! $arrayDimFetch->dim instanceof Variable) {
                    return false;
                }

                return $this->nodeNameResolver->isName($arrayDimFetch->dim, $keyValueName);
            }
        );
    }

    public function isValueVarUsedNext(Node $node, string $iteratedVariableSingle): bool
    {
        return (bool) $this->betterNodeFinder->findFirstNext($node, function (Node $node) use (
            $iteratedVariableSingle
        ): bool {
            if (! $node instanceof Variable) {
                return false;
            }
            return $this->nodeNameResolver->isName($node, $iteratedVariableSingle);
        });
    }

    /**
     * @param Expr[] $condExprs
     */
    public function isCondExprOneOrKeyValueNameNotNull(array $condExprs, ?string $keyValueName): bool
    {
        if (count($condExprs) !== 1) {
            return true;
        }

        return $keyValueName === null;
    }

    public function isArrayDimFetchPartOfAssignOrArgParentCount(ArrayDimFetch $arrayDimFetch): bool
    {
        $parentNode = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Node) {
            return false;
        }

        if ($this->assignManipulator->isNodePartOfAssign($parentNode)) {
            return true;
        }

        return $this->isArgParentCount($parentNode);
    }

    private function isArgParentCount(Node $node): bool
    {
        if (! $node instanceof Arg) {
            return false;
        }
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Node) {
            return false;
        }
        return $this->nodeNameResolver->isFuncCallName($parent, self::COUNT);
    }
}
