<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Maintainer\BinaryOpMaintainer;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ForeachToInArrayRector extends AbstractRector
{
    /**
     * @var Comment[]
     */
    private $comments = [];

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var BinaryOpMaintainer
     */
    private $binaryOpMaintainer;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, BinaryOpMaintainer $binaryOpMaintainer)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->binaryOpMaintainer = $binaryOpMaintainer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Simplify `foreach` loops into `in_array` when possible',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
foreach ($items as $item) {
    if ($item === "something") {
        return true;
    }
}

return false;
CODE_SAMPLE
                    ,
                    'in_array("something", $items, true);'
                ),
            ]
        );
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
        if ($this->shouldSkipForeach($node)) {
            return null;
        }

        /** @var If_ $firstNodeInsideForeach */
        $firstNodeInsideForeach = $node->stmts[0];
        if ($this->shouldSkipIf($firstNodeInsideForeach)) {
            return null;
        }

        /** @var Identical|Equal $ifCondition */
        $ifCondition = $firstNodeInsideForeach->cond;
        $foreachValueVar = $node->valueVar;

        $matchedNodes = $this->matchNodes($ifCondition, $foreachValueVar);
        if ($matchedNodes === null) {
            return null;
        }

        [, $comparedNode] = $matchedNodes;

        if (! $this->isIfBodyABoolReturnNode($firstNodeInsideForeach)) {
            return null;
        }

        $inArrayFunctionCall = $this->createInArrayFunction($comparedNode, $ifCondition, $node);

        /** @var Return_ $returnToRemove */
        $returnToRemove = $node->getAttribute(Attribute::NEXT_NODE);

        /** @var Return_ $return */
        $return = $firstNodeInsideForeach->stmts[0];
        if ($returnToRemove->expr === null) {
            return null;
        }

        if (! $this->isBool($returnToRemove->expr)) {
            return null;
        }

        if ($return->expr === null) {
            return null;
        }

        // cannot be "return true;" + "return true;"
        if ($this->areNodesEqual($return, $returnToRemove)) {
            return null;
        }

        $this->removeNode($returnToRemove);

        $return = new Return_($this->isFalse($return->expr) ? new BooleanNot(
            $inArrayFunctionCall
        ) : $inArrayFunctionCall);

        $this->combineCommentsToNode($node, $return);

        return $return;
    }

    private function shouldSkipForeach(Foreach_ $foreachNode): bool
    {
        if (isset($foreachNode->keyVar)) {
            return true;
        }

        if (count($foreachNode->stmts) > 1) {
            return true;
        }

        $nextNode = $foreachNode->getAttribute(Attribute::NEXT_NODE);
        if ($nextNode === null || ! $nextNode instanceof Return_) {
            return true;
        }

        $returnExpression = $nextNode->expr;

        if ($returnExpression === null) {
            return true;
        }

        if (! $this->isBool($returnExpression)) {
            return true;
        }

        return ! $foreachNode->stmts[0] instanceof If_;
    }

    private function shouldSkipIf(If_ $ifNode): bool
    {
        $ifCondition = $ifNode->cond;
        return ! $ifCondition instanceof Identical && ! $ifCondition instanceof Equal;
    }

    /**
     * @return Node[]|null
     */
    private function matchNodes(BinaryOp $binaryOp, Expr $expr): ?array
    {
        return $this->binaryOpMaintainer->matchFirstAndSecondConditionNode(
            $binaryOp,
            Variable::class,
            function (Node $node, Node $otherNode) use ($expr) {
                return $this->areNodesEqual($otherNode, $expr);
            }
        );
    }

    private function isIfBodyABoolReturnNode(If_ $firstNodeInsideForeach): bool
    {
        $ifStatment = $firstNodeInsideForeach->stmts[0];
        if (! $ifStatment instanceof Return_) {
            return false;
        }

        if ($ifStatment->expr === null) {
            return false;
        }

        return $this->isBool($ifStatment->expr);
    }

    /**
     * @param Identical|Equal $binaryOp
     */
    private function createInArrayFunction(Node $node, BinaryOp $binaryOp, Foreach_ $foreachNode): FuncCall
    {
        $arguments = $this->createArgs([$node, $foreachNode->expr]);

        if ($binaryOp instanceof Identical) {
            $arguments[] = $this->createArg($this->createTrue());
        }

        return $this->createFunction('in_array', $arguments);
    }

    /**
     * @todo decouple to CommentAttributeMaintainer service
     */
    private function combineCommentsToNode(Node $originalNode, Node $newNode): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable([$originalNode], function (Node $node): void {
            if ($node->hasAttribute('comments')) {
                $this->comments = array_merge($this->comments, $node->getComments());
            }
        });

        if ($this->comments === []) {
            return;
        }

        $commentContent = '';
        foreach ($this->comments as $comment) {
            $commentContent .= $comment->getText() . PHP_EOL;
        }

        $newNode->setAttribute('comments', [new Comment($commentContent)]);
    }
}
