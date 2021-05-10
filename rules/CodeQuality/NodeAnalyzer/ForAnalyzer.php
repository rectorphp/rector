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
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\Core\NodeManipulator\AssignManipulator
     */
    private $assignManipulator;
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\NodeManipulator\AssignManipulator $assignManipulator, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
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
        if ($condExprs[0] instanceof \PhpParser\Node\Expr\BinaryOp\Smaller) {
            if (!$this->nodeNameResolver->isName($condExprs[0]->left, $keyValueName)) {
                return \false;
            }
            return $this->nodeNameResolver->isName($condExprs[0]->right, $countValueName);
        }
        // $i > $count
        if ($condExprs[0] instanceof \PhpParser\Node\Expr\BinaryOp\Greater) {
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
        if ($prePostInc instanceof \PhpParser\Node\Expr\PreInc || $prePostInc instanceof \PhpParser\Node\Expr\PostInc) {
            return $this->nodeNameResolver->isName($prePostInc->var, $keyValueName);
        }
        return \false;
    }
    public function isCountValueVariableUsedInsideForStatements(\PhpParser\Node\Stmt\For_ $for, ?\PhpParser\Node\Expr $expr) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($for->stmts, function (\PhpParser\Node $node) use($expr) : bool {
            return $this->nodeComparator->areNodesEqual($node, $expr);
        });
    }
    public function isArrayWithKeyValueNameUnsetted(\PhpParser\Node\Stmt\For_ $for) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($for->stmts, function (\PhpParser\Node $node) : bool {
            /** @var Node $parent */
            $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$parent instanceof \PhpParser\Node\Stmt\Unset_) {
                return \false;
            }
            return $node instanceof \PhpParser\Node\Expr\ArrayDimFetch;
        });
    }
    public function isAssignmentWithArrayDimFetchAsVariableInsideForStatements(\PhpParser\Node\Stmt\For_ $for, string $keyValueName) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($for->stmts, function (\PhpParser\Node $node) use($keyValueName) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return \false;
            }
            if (!$node->var instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
                return \false;
            }
            $arrayDimFetch = $node->var;
            if ($arrayDimFetch->dim === null) {
                return \false;
            }
            if (!$arrayDimFetch->dim instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($arrayDimFetch->dim, $keyValueName);
        });
    }
    public function isValueVarUsedNext(\PhpParser\Node\Stmt\For_ $for, string $iteratedVariableSingle) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstNext($for, function (\PhpParser\Node $node) use($iteratedVariableSingle) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
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
    public function isArrayDimFetchPartOfAssignOrArgParentCount(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : bool
    {
        $parentNode = $arrayDimFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node) {
            return \false;
        }
        if ($this->assignManipulator->isNodePartOfAssign($parentNode)) {
            return \true;
        }
        return $this->isArgParentCount($parentNode);
    }
    private function isArgParentCount(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Arg) {
            return \false;
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Expr\FuncCall) {
            return \false;
        }
        return $this->nodeNameResolver->isName($parent, self::COUNT);
    }
}
