<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\CodeQuality;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Ternary;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class UnnecessaryTernaryExpressionRector extends AbstractRector
{
    /**
     * @var string
     */
    private $ifValue;

    /**
     * @var string
     */
    private $elseValue;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Remove unnecessary ternary expressions.',
            [new CodeSample('$foo === $bar ? true : false;', '$foo === $bar;')]
        );
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Ternary) {
            return false;
        }

        /** @var Ternary $ternaryExpression */
        $ternaryExpression = $node;

        if (! $ternaryExpression->if instanceof Expr) {
            return false;
        }

        $condition = $ternaryExpression->cond;
        if (! $condition instanceof BinaryOp) {
            return false;
        }

        $ifExpression = $ternaryExpression->if;
        $elseExpression = $ternaryExpression->else;

        if (! $ifExpression instanceof ConstFetch
            && ! $elseExpression instanceof ConstFetch
        ) {
            return false;
        }

        $this->ifValue = $ifExpression->name->toLowerString();
        $this->elseValue = $elseExpression->name->toLowerString();

        return ! in_array('null', [$this->ifValue, $this->elseValue], true);
    }

    /**
     * @param Ternary $ternaryExpression
     */
    public function refactor(Node $ternaryExpression): ?Node
    {
        /** @var BinaryOp */
        $binaryOpOperation = $ternaryExpression->cond;

        if ($this->ifValue === 'true' && $this->elseValue === 'false') {
            $ternaryExpression = $binaryOpOperation;
        } else {
            $ternaryExpression = $this->fixBinaryOperation($binaryOpOperation);
        }

        return $ternaryExpression;
    }

    private function fixBinaryOperation(BinaryOp $operation): BinaryOp
    {
        $operandsMap = [
            '===' => 'NotIdentical',
            '!==' => 'Identical',
            '==' => 'NotEqual',
            '!=' => 'Equal',
            '<>' => 'Equal',
            '>' => 'Smaller',
            '<' => 'Greater',
            '>=' => 'SmallerOrEqual',
            '<=' => 'GreaterOrEqual',
        ];

        $operand = $operation->getOperatorSigil();
        $binaryOpClassName = 'PhpParser\\Node\\Expr\\BinaryOp\\' . $operandsMap[$operand];

        return new $binaryOpClassName($operation->left, $operation->right);
    }
}
