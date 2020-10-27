<?php

declare(strict_types=1);

namespace Rector\Performance\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\CountArrayToEmptyArrayComparisonRectorTest
 */
final class CountArrayToEmptyArrayComparisonRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change count array comparison to empty array comparison to improve performance', [
            new CodeSample(
                <<<'CODE_SAMPLE'
count($array) === 0;
count($array) > 0;
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
$array === [];
$array !== [];
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $functionName = $this->getName($node);
        if ($functionName === null || $functionName !== 'count') {
            return null;
        }

        /** @var Variable|MethodCall $expr */
        $expr = $node->args[0]->value;

        /** @var Scope $scope */
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        $type = $scope->getType($expr);

        // not pass array type, skip
        if (! $type instanceof ArrayType) {
            return null;
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);

        if (! $parent instanceof Identical && ! $parent instanceof Greater && ! $parent instanceof Smaller) {
            return null;
        }

        $processIdentical = $this->processIdentical($parent, $node, $expr);
        if ($processIdentical !== null) {
            return $processIdentical;
        }

        return $this->processGreaterOrSmaller($parent, $node, $expr);
    }

    private function processIdentical(BinaryOp $binaryOp, FuncCall $funcCall, Expr $expr): ?Expr
    {
        if ($binaryOp instanceof Identical && $binaryOp->right instanceof LNumber && $binaryOp->right->value === 0) {
            $this->removeNode($funcCall);
            $binaryOp->right = new Array_([]);

            return $expr;
        }

        if ($binaryOp instanceof Identical && $binaryOp->left instanceof LNumber && $binaryOp->left->value === 0) {
            $this->removeNode($funcCall);
            $binaryOp->left = new Array_([]);

            return $expr;
        }

        return null;
    }

    private function processGreaterOrSmaller(BinaryOp $binaryOp, FuncCall $funcCall, Expr $expr): ?NotIdentical
    {
        if ($binaryOp instanceof Greater && $binaryOp->right instanceof LNumber && $binaryOp->right->value === 0) {
            $this->removeNode($funcCall);
            $this->removeNode($binaryOp->right);

            return new NotIdentical($expr, new Array_([]));
        }

        if ($binaryOp instanceof Smaller && $binaryOp->left instanceof LNumber && $binaryOp->left->value === 0) {
            $this->removeNode($funcCall);
            $this->removeNode($binaryOp->left);

            return new NotIdentical(new Array_([]), $expr);
        }

        return null;
    }
}
