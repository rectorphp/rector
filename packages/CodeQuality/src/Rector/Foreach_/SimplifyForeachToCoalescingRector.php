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
use Rector\NodeAnalyzer\ForeachAnalyzer;
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

    /**
     * @var ForeachAnalyzer
     */
    private $foreachAnalyzer;

    public function __construct(ForeachAnalyzer $foreachAnalyzer)
    {
        $this->foreachAnalyzer = $foreachAnalyzer;
    }

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
        if (! $node->keyVar) {
            return null;
        }

        /** @var Return_|Assign|null $returnOrAssignNode */
        $returnOrAssignNode = $this->matchReturnOrAssignNode($node);
        if (! $returnOrAssignNode) {
            return null;
        }

        // return $newValue;
        // we don't return the node value
        if (! $this->areNodesEqual($node->valueVar, $returnOrAssignNode->expr)) {
            return null;
        }

        if ($returnOrAssignNode instanceof Return_) {
            return $this->processForeachNodeWithReturnInside($node, $returnOrAssignNode);
        }

        return $this->processForeachNodeWithAssignInside($node, $returnOrAssignNode);
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

    /**
     * @return Assign|Return_|null
     */
    private function matchReturnOrAssignNode(Foreach_ $foreachNode): ?Node
    {
        return $this->foreachAnalyzer->matchOnlyStmt($foreachNode, function (Node $node): ?Node {
            if (! $node instanceof If_) {
                return null;
            }

            if (! $node->cond instanceof Identical) {
                return null;
            }

            if (count($node->stmts) !== 1) {
                return null;
            }

            if ($node->stmts[0] instanceof Expression) {
                $innerNode = $node->stmts[0]->expr;
            } else {
                $innerNode = $node->stmts[0];
            }

            if ($innerNode instanceof Assign || $innerNode instanceof Return_) {
                return $innerNode;
            }

            return null;
        });
    }
}
