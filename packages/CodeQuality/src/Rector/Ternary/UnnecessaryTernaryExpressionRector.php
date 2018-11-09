<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
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

        $condition = $ternaryExpression->cond;
        if (! $condition instanceof BinaryOp) {
            return null;
        }

        $ifExpression = $ternaryExpression->if;
        $elseExpression = $ternaryExpression->else;
        if (! $this->isBool($ifExpression) || ! $this->isBool($elseExpression)) {
            return null;
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
}
