<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Ternary;
use Rector\PhpParser\Node\AssignAndBinaryMap;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class UnnecessaryTernaryExpressionRector extends AbstractRector
{
    /**
     * @var AssignAndBinaryMap
     */
    private $assignAndBinaryMap;

    public function __construct(AssignAndBinaryMap $assignAndBinaryMap)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Remove unnecessary ternary expressions.',
            [new CodeSample('$foo === $bar ? true : false;', '$foo === $bar;')]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Ternary::class];
    }

    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var Ternary $ternaryExpression */
        $ternaryExpression = $node;
        if (! $ternaryExpression->if instanceof Expr) {
            return null;
        }

        $ifExpression = $ternaryExpression->if;
        if ($ifExpression === null) {
            return null;
        }

        $elseExpression = $ternaryExpression->else;
        if (! $this->isBool($ifExpression) || ! $this->isBool($elseExpression)) {
            return null;
        }

        $condition = $ternaryExpression->cond;
        if (! $condition instanceof BinaryOp) {
            return $this->processNonBinaryCondition($ifExpression, $elseExpression, $condition);
        }

        if ($this->isNull($ifExpression) || $this->isNull($elseExpression)) {
            return null;
        }

        /** @var BinaryOp $binaryOperation */
        $binaryOperation = $node->cond;

        if ($this->isTrue($ifExpression) && $this->isFalse($elseExpression)) {
            return $binaryOperation;
        }

        $inversedBinaryClass = $this->assignAndBinaryMap->getInversed($binaryOperation);
        if ($inversedBinaryClass === null) {
            return null;
        }

        return new $inversedBinaryClass($binaryOperation->left, $binaryOperation->right);
    }

    private function processNonBinaryCondition(Expr $ifExpression, Expr $elseExpression, Expr $condition): ?Node
    {
        if ($this->isTrue($ifExpression) && $this->isFalse($elseExpression)) {
            if ($this->isBoolType($condition)) {
                return $condition;
            }

            return new Bool_($condition);
        }

        if ($this->isFalse($ifExpression) && $this->isTrue($elseExpression)) {
            if ($this->isBoolType($condition)) {
                return new BooleanNot($condition);
            }

            return new BooleanNot(new Bool_($condition));
        }

        return null;
    }
}
