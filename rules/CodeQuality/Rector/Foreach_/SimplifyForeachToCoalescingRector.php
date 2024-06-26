<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector\SimplifyForeachToCoalescingRectorTest
 */
final class SimplifyForeachToCoalescingRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes foreach that returns set value to ??', [new CodeSample(<<<'CODE_SAMPLE'
foreach ($this->oldToNewFunctions as $oldFunction => $newFunction) {
    if ($currentFunction === $oldFunction) {
        return $newFunction;
    }
}

return null;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return $this->oldToNewFunctions[$currentFunction] ?? null;
CODE_SAMPLE
)]);
    }
    /**
     * @innerForeachReturn array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Foreach_) {
                continue;
            }
            $foreach = $stmt;
            if (!$foreach->keyVar instanceof Expr) {
                continue;
            }
            $foreachReturnOrAssign = $this->matchForeachReturnOrAssign($foreach);
            if ($foreachReturnOrAssign instanceof Expression) {
                /** @var Assign $innerAssign */
                $innerAssign = $foreachReturnOrAssign->expr;
                if (!$this->nodeComparator->areNodesEqual($foreach->valueVar, $innerAssign->expr)) {
                    return null;
                }
                $assign = $this->processForeachNodeWithAssignInside($foreach, $innerAssign);
                if (!$assign instanceof Assign) {
                    return null;
                }
                $node->stmts[$key] = new Expression($assign);
                $hasChanged = \true;
                continue;
            }
            if ($foreachReturnOrAssign instanceof Return_) {
                if (!$this->nodeComparator->areNodesEqual($foreach->valueVar, $foreachReturnOrAssign->expr)) {
                    return null;
                }
                $nextStmt = $node->stmts[$key + 1] ?? null;
                if (!$nextStmt instanceof Return_) {
                    continue;
                }
                $return = $this->processForeachNodeWithReturnInside($foreach, $foreachReturnOrAssign, $nextStmt);
                if (!$return instanceof Return_) {
                    continue;
                }
                $node->stmts[$key] = $return;
                // cleanup next return
                unset($node->stmts[$key + 1]);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULL_COALESCE;
    }
    /**
     * @todo make assign expr generic
     * @return Expression<Assign>|Return_|null
     */
    private function matchForeachReturnOrAssign(Foreach_ $foreach)
    {
        if (\count($foreach->stmts) !== 1) {
            return null;
        }
        $onlyForeachStmt = $foreach->stmts[0];
        if (!$onlyForeachStmt instanceof If_) {
            return null;
        }
        $if = $onlyForeachStmt;
        if (!$if->cond instanceof Identical) {
            return null;
        }
        if (\count($if->stmts) !== 1) {
            return null;
        }
        if ($if->else instanceof Else_ || $if->elseifs !== []) {
            return null;
        }
        $innerStmt = $if->stmts[0];
        if ($innerStmt instanceof Return_) {
            return $innerStmt;
        }
        if (!$innerStmt instanceof Expression) {
            return null;
        }
        $innerNode = $innerStmt->expr;
        if ($innerNode instanceof Assign) {
            if ($innerNode->var instanceof ArrayDimFetch) {
                return null;
            }
            return $innerStmt;
        }
        return null;
    }
    private function processForeachNodeWithReturnInside(Foreach_ $foreach, Return_ $innerForeachReturn, ?Stmt $nextStmt) : ?\PhpParser\Node\Stmt\Return_
    {
        if (!$this->nodeComparator->areNodesEqual($foreach->valueVar, $innerForeachReturn->expr)) {
            return null;
        }
        /** @var If_ $ifNode */
        $ifNode = $foreach->stmts[0];
        /** @var Identical $identicalNode */
        $identicalNode = $ifNode->cond;
        if ($this->nodeComparator->areNodesEqual($identicalNode->left, $foreach->keyVar)) {
            $checkedNode = $identicalNode->right;
        } elseif ($this->nodeComparator->areNodesEqual($identicalNode->right, $foreach->keyVar)) {
            $checkedNode = $identicalNode->left;
        } else {
            return null;
        }
        $coalesce = new Coalesce(new ArrayDimFetch($foreach->expr, $checkedNode), $nextStmt instanceof Return_ && $nextStmt->expr instanceof Expr ? $nextStmt->expr : $checkedNode);
        return new Return_($coalesce);
    }
    private function processForeachNodeWithAssignInside(Foreach_ $foreach, Assign $assign) : ?Assign
    {
        /** @var If_ $ifNode */
        $ifNode = $foreach->stmts[0];
        /** @var Identical $identicalNode */
        $identicalNode = $ifNode->cond;
        if ($this->nodeComparator->areNodesEqual($identicalNode->left, $foreach->keyVar)) {
            $checkedNode = $assign->var;
            $keyNode = $identicalNode->right;
        } elseif ($this->nodeComparator->areNodesEqual($identicalNode->right, $foreach->keyVar)) {
            $checkedNode = $assign->var;
            $keyNode = $identicalNode->left;
        } else {
            return null;
        }
        $arrayDimFetch = new ArrayDimFetch($foreach->expr, $keyNode);
        return new Assign($checkedNode, new Coalesce($arrayDimFetch, $checkedNode));
    }
}
