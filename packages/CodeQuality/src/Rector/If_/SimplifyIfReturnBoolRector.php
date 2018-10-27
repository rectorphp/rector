<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyIfReturnBoolRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Shortens if return false/true to direct return', [
            new CodeSample(
                <<<'CODE_SAMPLE'
if (strpos($docToken->getContent(), "\n") === false) {
    return true;
}

return false;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
return strpos($docToken->getContent(), "\n") === false;
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var Return_ $ifInnerNode */
        $ifInnerNode = $node->stmts[0];

        /** @var Return_ $nextNode */
        $nextNode = $node->getAttribute(Attribute::NEXT_NODE);

        if ($this->isTrue($ifInnerNode->expr)) {
            $newReturnNode = $this->processReturnTrue($node, $nextNode);
        } elseif ($this->isFalse($ifInnerNode->expr)) {
            $newReturnNode = $this->processReturnFalse($node, $nextNode);
        }

        if (isset($newReturnNode) && $newReturnNode !== null) {
            $this->keepComments($node, $newReturnNode);
            $this->removeNode($nextNode);

            return $newReturnNode;
        }

        return null;
    }

    private function shouldSkip(If_ $ifNode): bool
    {
        if (count($ifNode->stmts) !== 1) {
            return true;
        }

        $ifInnerNode = $ifNode->stmts[0];
        if (! $ifInnerNode instanceof Return_) {
            return true;
        }

        if ($ifInnerNode->expr === null) {
            return true;
        }

        if (! $this->isBool($ifInnerNode->expr)) {
            return true;
        }

        $nextNode = $ifNode->getAttribute(Attribute::NEXT_NODE);
        if (! $nextNode instanceof Return_) {
            return true;
        }
        return ! $this->isBool($nextNode->expr);
    }

    private function processReturnTrue(If_ $ifNode, Return_ $nextReturnNode): Return_
    {
        if ($ifNode->cond instanceof BooleanNot && $this->isTrue($nextReturnNode->expr)) {
            return new Return_($this->boolCastIfNeeded($ifNode->cond->expr));
        }

        return new Return_($this->boolCastIfNeeded($ifNode->cond));
    }

    private function processReturnFalse(If_ $ifNode, Return_ $nextReturnNode): ?Return_
    {
        if ($ifNode->cond instanceof Identical) {
            return new Return_($this->boolCastIfNeeded(new NotIdentical($ifNode->cond->left, $ifNode->cond->right)));
        }

        if ($this->isTrue($nextReturnNode->expr)) {
            if ($ifNode->cond instanceof BooleanNot) {
                return new Return_($this->boolCastIfNeeded($ifNode->cond->expr));
            }

            return new Return_($this->boolCastIfNeeded(new BooleanNot($ifNode->cond)));
        }

        return null;
    }

    private function keepComments(Node $oldNode, Node $newNode): void
    {
        if ($oldNode->getDocComment()) {
            $newNode->setDocComment($oldNode->getDocComment());
        }

        $newNode->setAttribute('comments', $oldNode->getComments());
    }

    private function boolCastIfNeeded(Expr $exprNode): Expr
    {
        if ($exprNode instanceof BooleanNot) {
            return $exprNode;
        }

        if ($this->isBoolType($exprNode)) {
            return $exprNode;
        }

        return new Bool_($exprNode);
    }
}
