<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\CodeQuality;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Identifier;
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
            || ! $elseExpression instanceof ConstFetch
        ) {
            return false;
        }

        /** @var Identifier $ifExpressionName */
        $ifExpressionName = $ifExpression->name;
        /** @var Identifier $elseExpressionName */
        $elseExpressionName = $elseExpression->name;

        $this->ifValue = $ifExpressionName->toLowerString();
        $this->elseValue = $elseExpressionName->toLowerString();

        return ! in_array('null', [$this->ifValue, $this->elseValue], true);
    }

    /**
     * @param Ternary $ternaryNode
     */
    public function refactor(Node $ternaryNode): ?Node
    {
        /** @var BinaryOp */
        $binaryOpOperation = $ternaryNode->cond;

        if ($this->ifValue === 'true' && $this->elseValue === 'false') {
            $ternaryNode = $binaryOpOperation;
        } else {
            $ternaryNode = $this->fixBinaryOperation($binaryOpOperation);
        }

        return $ternaryNode;
    }

    private function fixBinaryOperation(BinaryOp $operation): BinaryOp
    {
        $operandsMap = [
            '===' => NotIdentical::class,
            '!==' => Identical::class,
            '==' => NotEqual::class,
            '!=' => Equal::class,
            '<>' => Equal::class,
            '>' => Smaller::class,
            '<' => Greater::class,
            '>=' => SmallerOrEqual::class,
            '<=' => GreaterOrEqual::class,
        ];

        $operand = $operation->getOperatorSigil();
        $binaryOpClassName = $operandsMap[$operand];

        return new $binaryOpClassName($operation->left, $operation->right);
    }
}
