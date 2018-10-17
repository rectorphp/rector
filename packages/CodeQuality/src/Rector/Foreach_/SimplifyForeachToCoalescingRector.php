<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyForeachToCoalescingRector extends AbstractRector
{
    /**
     * @var Return_|null
     */
    private $returnNode;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes foreach that returns set value to ??', [
            new CodeSample(
                <<<'CODE_SAMPLE'
foreach ($this->oldToNewFunctions as $oldFunction => $newFunction) {
    if ($currentFunction === $oldFunction) {
        return $newFunction;
    }
}

return null;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
return $this->oldToNewFunctions[$currentFunction] ?? null;
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        return $this->processForeachNode($node);
    }

    private function shouldSkip(Foreach_ $foreachNode): bool
    {
        if (! $foreachNode->keyVar) {
            return true;
        }

        if (count($foreachNode->stmts) !== 1) {
            return true;
        }

        $insideForeachStmt = $foreachNode->stmts[0];
        if (! $insideForeachStmt instanceof If_) {
            return true;
        }

        if (! $insideForeachStmt->cond instanceof Identical) {
            return true;
        }

        if (count($insideForeachStmt->stmts) !== 1) {
            return true;
        }

        return false;
    }

    private function processForeachNode(Foreach_ $node): ?Node
    {
        /** @var If_ $insideForeachStmt */
        $insideForeachStmt = $node->stmts[0];

        $returnOrAssignNode = $insideForeachStmt->stmts[0];

        /** @var Return_|Assign|null $insideReturnOrAssignNode */
        $insideReturnOrAssignNode = $returnOrAssignNode instanceof Expression ? $returnOrAssignNode->expr : $returnOrAssignNode;
        if ($insideReturnOrAssignNode === null) {
            return null;
        }

        // return $newValue;
        // we don't return the node value
        if (! $this->areNodesEqual($node->valueVar, $insideReturnOrAssignNode->expr)) {
            return null;
        }

        if ($insideReturnOrAssignNode instanceof Return_) {
            return $this->processForeachNodeWithReturnInside($node, $insideReturnOrAssignNode);
        }

        if ($insideReturnOrAssignNode instanceof Assign) {
            return $this->processForeachNodeWithAssignInside($node, $insideReturnOrAssignNode);
        }
    }

    private function processForeachNodeWithReturnInside(Foreach_ $foreachNode, Return_ $returnNode): ?Node
    {
        if (! $this->areNodesEqual($foreachNode->valueVar, $returnNode->expr)) {
            return null;
        }

        /** @var If_ $ifNode */
        $ifNode = $foreachNode->stmts[0];

        /** @var Identical $identicalNode */
        $identicalNode = $ifNode->cond;

        if ($this->areNodesEqual($identicalNode->left, $foreachNode->keyVar)) {
            $checkedNode = $identicalNode->right;
        } elseif ($this->areNodesEqual($identicalNode->right, $foreachNode->keyVar)) {
            $checkedNode = $identicalNode->left;
        } else {
            return null;
        }

        // is next node Return?
        if ($foreachNode->getAttribute(Attribute::NEXT_NODE) instanceof Return_) {
            $this->returnNode = $foreachNode->getAttribute(Attribute::NEXT_NODE);
            $this->removeNode($this->returnNode);
        }

        $coalesceNode = new Coalesce(new ArrayDimFetch(
            $foreachNode->expr,
            $checkedNode
        ), $this->returnNode ? $this->returnNode->expr : $checkedNode);

        if ($this->returnNode) {
            return new Return_($coalesceNode);
        }

        return null;
    }

    private function processForeachNodeWithAssignInside(Foreach_ $foreachNode, Assign $assignNode): ?Node
    {
        /** @var If_ $ifNode */
        $ifNode = $foreachNode->stmts[0];

        /** @var Identical $identicalNode */
        $identicalNode = $ifNode->cond;

        if ($this->areNodesEqual($identicalNode->left, $foreachNode->keyVar)) {
            $checkedNode = $assignNode->var;
        } elseif ($this->areNodesEqual($identicalNode->right, $foreachNode->keyVar)) {
            $checkedNode = $assignNode->var;
        } else {
            return null;
        }

        $assignNode = new Assign($checkedNode, new Coalesce(new ArrayDimFetch(
            $foreachNode->expr,
            $foreachNode->valueVar
        ), $checkedNode));

        return new Expression($assignNode);
    }
}
