<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Greater;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Smaller;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\PostInc;
use RectorPrefix20220606\PhpParser\Node\Expr\PreInc;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Stmt\For_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Unset_;
use RectorPrefix20220606\Rector\Core\NodeManipulator\AssignManipulator;
use RectorPrefix20220606\Rector\Core\PhpParser\Comparing\NodeComparator;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
final class ForAnalyzer
{
    /**
     * @var string
     */
    private const COUNT = 'count';
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\AssignManipulator
     */
    private $assignManipulator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder, AssignManipulator $assignManipulator, NodeComparator $nodeComparator)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->assignManipulator = $assignManipulator;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param Expr[] $condExprs
     */
    public function isCondExprSmallerOrGreater(array $condExprs, string $keyValueName, string $countValueName) : bool
    {
        // $i < $count
        if ($condExprs[0] instanceof Smaller) {
            if (!$this->nodeNameResolver->isName($condExprs[0]->left, $keyValueName)) {
                return \false;
            }
            return $this->nodeNameResolver->isName($condExprs[0]->right, $countValueName);
        }
        // $i > $count
        if ($condExprs[0] instanceof Greater) {
            if (!$this->nodeNameResolver->isName($condExprs[0]->left, $countValueName)) {
                return \false;
            }
            return $this->nodeNameResolver->isName($condExprs[0]->right, $keyValueName);
        }
        return \false;
    }
    /**
     * @param Expr[] $loopExprs
     * $param
     */
    public function isLoopMatch(array $loopExprs, ?string $keyValueName) : bool
    {
        if (\count($loopExprs) !== 1) {
            return \false;
        }
        if ($keyValueName === null) {
            return \false;
        }
        $prePostInc = $loopExprs[0];
        if ($prePostInc instanceof PreInc || $prePostInc instanceof PostInc) {
            return $this->nodeNameResolver->isName($prePostInc->var, $keyValueName);
        }
        return \false;
    }
    public function isCountValueVariableUsedInsideForStatements(For_ $for, ?Expr $expr) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($for->stmts, function (Node $node) use($expr) : bool {
            return $this->nodeComparator->areNodesEqual($node, $expr);
        });
    }
    public function isArrayWithKeyValueNameUnsetted(For_ $for) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($for->stmts, function (Node $node) : bool {
            /** @var Node $parent */
            $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
            if (!$parent instanceof Unset_) {
                return \false;
            }
            return $node instanceof ArrayDimFetch;
        });
    }
    public function isAssignmentWithArrayDimFetchAsVariableInsideForStatements(For_ $for, string $keyValueName) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($for->stmts, function (Node $node) use($keyValueName) : bool {
            if (!$node instanceof Assign) {
                return \false;
            }
            if (!$node->var instanceof ArrayDimFetch) {
                return \false;
            }
            $arrayDimFetch = $node->var;
            if ($arrayDimFetch->dim === null) {
                return \false;
            }
            if (!$arrayDimFetch->dim instanceof Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($arrayDimFetch->dim, $keyValueName);
        });
    }
    public function isValueVarUsedNext(For_ $for, string $iteratedVariableSingle) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstNext($for, function (Node $node) use($iteratedVariableSingle) : bool {
            if (!$node instanceof Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $iteratedVariableSingle);
        });
    }
    /**
     * @param Expr[] $condExprs
     */
    public function isCondExprOneOrKeyValueNameNotNull(array $condExprs, ?string $keyValueName) : bool
    {
        if (\count($condExprs) !== 1) {
            return \true;
        }
        return $keyValueName === null;
    }
    public function isArrayDimFetchPartOfAssignOrArgParentCount(ArrayDimFetch $arrayDimFetch) : bool
    {
        $parentNode = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Node) {
            return \false;
        }
        if ($this->assignManipulator->isNodePartOfAssign($parentNode)) {
            return \true;
        }
        return $this->isArgParentCount($parentNode);
    }
    private function isArgParentCount(Node $node) : bool
    {
        if (!$node instanceof Arg) {
            return \false;
        }
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parent instanceof FuncCall) {
            return \false;
        }
        return $this->nodeNameResolver->isName($parent, self::COUNT);
    }
}
