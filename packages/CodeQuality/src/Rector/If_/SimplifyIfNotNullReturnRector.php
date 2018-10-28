<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyIfNotNullReturnRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes redundant null check to instant return', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$newNode = 'something ;
if ($newNode !== null) {
    return $newNode;
}

return null;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$newNode = 'something ;
return $newNode;
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->stmts) !== 1) {
            return null;
        }

        $insideIfNode = $node->stmts[0];
        if (! $insideIfNode instanceof Return_) {
            return null;
        }

        $nextNode = $node->getAttribute(Attribute::NEXT_NODE);
        if (! $nextNode instanceof Return_) {
            return null;
        }

        if ($node->cond instanceof NotIdentical && $this->areCrossNodesEquals($node->cond, $nextNode, $insideIfNode)) {
            $this->removeNode($nextNode);
            return $insideIfNode;
        }

        if ($node->cond instanceof Identical && $this->areCrossNodesEquals($node->cond, $nextNode, $insideIfNode)) {
            $this->removeNode($nextNode);
            return clone $nextNode;
        }

        return null;
    }

    private function areCrossNodesEquals(BinaryOp $condNode, Return_ $thirdNode, Return_ $fourthNode): bool
    {
        if ($this->areNodesEqual($condNode->left, $thirdNode->expr) && $this->areNodesEqual(
            $condNode->right,
            $fourthNode->expr
        )) {
            return true;
        }

        return $this->areNodesEqual($condNode->right, $thirdNode->expr) && $this->areNodesEqual(
            $condNode->left,
            $fourthNode->expr
        );
    }
}
