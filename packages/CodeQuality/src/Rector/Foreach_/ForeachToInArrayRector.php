<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Comment;
use PhpParser\Node;
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
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Utils\NodeTraverser\CallableNodeTraverser;

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
        if (! $this->shouldSkipForeach($node)) {
            return null;
        }

        /** @var If_ $firstNodeInsideForeach */
        $firstNodeInsideForeach = $node->stmts[0];
        if ($this->shouldSkipIf($firstNodeInsideForeach)) {
            return null;
        }

        /** @var BinaryOp $ifCondition */
        $ifCondition = $firstNodeInsideForeach->cond;
        $foreachValueNode = $node->valueVar;

        $matchedNodes = $this->binaryOpMaintainer->matchFirstAndSecondConditionNode(
            $ifCondition,
            function (Node $node) {
                return $node instanceof Variable;
            },
            function (Node $node, Node $otherNode) use ($foreachValueNode) {
                return $this->areNodesEqual($otherNode, $foreachValueNode);
            }
        );

        if ($matchedNodes === null) {
            return null;
        }

        [, $comparedNode] = $matchedNodes;

        if (! $this->isIfBodyABoolReturnNode($firstNodeInsideForeach)) {
            return null;
        }

        $inArrayFunctionCall = $this->createInArrayFunction($comparedNode, $ifCondition, $node);

        /** @var Return_ $returnNodeToRemove */
        $returnNodeToRemove = $node->getAttribute(Attribute::NEXT_NODE);

        /** @var Return_ $returnNode */
        $returnNode = $firstNodeInsideForeach->stmts[0];

        if (! $this->isBool($returnNodeToRemove->expr)) {
            return null;
        }

        if ($this->areNodesEqual($returnNode, $returnNodeToRemove)) {
            return null;
        }

        $this->removeNode($returnNodeToRemove);

        $returnNode = new Return_($this->isFalse($returnNode->expr) ? new BooleanNot(
            $inArrayFunctionCall
        ) : $inArrayFunctionCall);

        $this->combineCommentsToNode($node, $returnNode);

        return $returnNode;
    }

    private function shouldSkipForeach(Foreach_ $foreachNode): bool
    {
        if (isset($foreachNode->keyVar)) {
            return false;
        }

        if (count($foreachNode->stmts) > 1) {
            return false;
        }

        $nextNode = $foreachNode->getAttribute(Attribute::NEXT_NODE);
        if ($nextNode === null || ! $nextNode instanceof Return_) {
            return false;
        }

        $returnExpression = $nextNode->expr;

        if ($returnExpression !== null && $this->isBool($returnExpression)) {
            return true;
        }

        return $foreachNode->stmts[0] instanceof If_;
    }

    private function isIfBodyABoolReturnNode(If_ $firstNodeInsideForeach): bool
    {
        $ifStatment = $firstNodeInsideForeach->stmts[0];
        if (! $ifStatment instanceof Return_) {
            return false;
        }

        return $this->isBool($ifStatment->expr);
    }

    /**
     * @param Identical|Equal $ifCondition
     */
    private function createInArrayFunction(Node $condition, BinaryOp $ifCondition, Foreach_ $foreachNode): FuncCall
    {
        $arguments = $this->createArgs([$condition, $foreachNode->expr]);

        if ($ifCondition instanceof Identical) {
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

    private function shouldSkipIf(If_ $ifNode): bool
    {
        $ifCondition = $ifNode->cond;
        if (! $ifCondition instanceof Identical && ! $ifCondition instanceof Equal) {
            return true;
        }

        return false;
    }
}
