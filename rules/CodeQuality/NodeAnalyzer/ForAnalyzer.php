<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Unset_;
use Rector\Core\NodeManipulator\AssignManipulator;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
        return (bool) $this->betterNodeFinder->findFirst($for->stmts, static function (Node $node) : bool {
            /** @var Node $parentNode */
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if (!$parentNode instanceof Unset_) {
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
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof FuncCall) {
            return \false;
        }
        return $this->nodeNameResolver->isName($parentNode, self::COUNT);
    }
}
