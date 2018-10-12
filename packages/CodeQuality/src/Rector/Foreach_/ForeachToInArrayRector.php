<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\ConstFetchAnalyzer;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ForeachToInArrayRector extends AbstractRector
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var ConstFetchAnalyzer
     */
    private $constFetchAnalyzer;

    /**
     * @var Return_
     */
    private $returnNodeToRemove;

    public function __construct(NodeFactory $nodeFactory, ConstFetchAnalyzer $constFetchAnalyzer)
    {
        $this->nodeFactory = $nodeFactory;
        $this->constFetchAnalyzer = $constFetchAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Simplify `foreach` loops into `in_array` when possible',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
foreach ($items as $item) {
    if ($item === 'something') {
        return true;
    }
}

return false;
CODE_SAMPLE
                    ,
                    "in_array('something', \$items, true);"
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
     * @param Foreach_ $foreachNode
     */
    public function refactor(Node $foreachNode): ?Node
    {
        if (! $this->isAForeachCandidate($foreachNode)) {
            return null;
        }

        $firstNodeInsideForeach = $foreachNode->stmts[0];

        if (! $firstNodeInsideForeach instanceof If_) {
            return null;
        }

        $ifCondition = $firstNodeInsideForeach->cond;

        if (! $ifCondition instanceof Identical && ! $ifCondition instanceof Equal) {
            return null;
        }

        $leftVariable = $ifCondition->left;
        $rightVariable = $ifCondition->right;

        if (! $leftVariable instanceof Variable & ! $rightVariable instanceof Variable) {
            return null;
        }

        $condition = $this->normalizeYodaComparison($leftVariable, $rightVariable, $foreachNode);

        if (! $this->isIfBodyABoolReturnNode($firstNodeInsideForeach)) {
            return null;
        }

        $inArrayFunctionCall = $this->createInArrayFunction($condition, $ifCondition, $foreachNode);

        $this->returnNodeToRemove = $foreachNode->getAttribute(Attribute::NEXT_NODE);
        $this->removeNode($this->returnNodeToRemove);

        $negativeReturn = $this->constFetchAnalyzer->isFalse($firstNodeInsideForeach->stmts[0]->expr);

        return new Return_($negativeReturn ? new BooleanNot($inArrayFunctionCall) : $inArrayFunctionCall);
    }

    /**
     * @param mixed $leftValue
     * @param mixed $rightValue
     *
     * @return mixed
     */
    private function normalizeYodaComparison($leftValue, $rightValue, Foreach_ $foreachNode)
    {
        if ($leftValue instanceof Variable) {
            if ($leftValue->name === $foreachNode->valueVar->name) {
                return $rightValue;
            }
        }
        
        if ($rightValue->name === $foreachNode->valueVar->name) {
            return $leftValue;
        }
    }

    /**
     * @param mixed $condition
     * @param Identical|Equal $ifCondition
     */
    private function createInArrayFunction($condition, $ifCondition, Foreach_ $foreachNode): FuncCall
    {
        $arguments = $this->nodeFactory->createArgs([$condition, $foreachNode->expr]);

        if ($ifCondition instanceof Identical) {
            $arguments[] = $this->nodeFactory->createArg($this->nodeFactory->createTrueConstant());
        }

        return new FuncCall(new Name('in_array'), $arguments);
    }

    private function isIfBodyABoolReturnNode(If_ $firstNodeInsideForeach): bool
    {
        $ifStatment = $firstNodeInsideForeach->stmts[0];

        if (! $ifStatment instanceof Return_) {
            return false;
        }

        return $this->constFetchAnalyzer->isBool($ifStatment->expr);
    }

    private function isAForeachCandidate(Foreach_ $foreachNode): bool
    {
        if (isset($foreachNode->keyVar)) {
            return false;
        }

        $nextNode = $foreachNode->getAttribute(Attribute::NEXT_NODE);
        if ($nextNode === null) {
            return false;
        }

        $nextNode = $nextNode->expr;

        return $nextNode !== null && $this->constFetchAnalyzer->isBool($nextNode);
    }
}
