<?php

declare(strict_types=1);

namespace Rector\Performance\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
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

        /** @var Expr $expr */
        $expr = $node->args[0]->value;
        /** @var Scope|null $scope */
        $scope = $expr->getAttribute(AttributeKey::SCOPE);

        if (! $scope instanceof Scope) {
            return null;
        }

        $type = $scope->getType($expr);

        // not pass array type, skip
        if (! $type instanceof ArrayType) {
            return null;
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);

        if (! $parent instanceof Node) {
            return null;
        }

        $processIdentical = $this->processIdentical($parent, $node, $expr);
        if ($processIdentical !== null) {
            return $processIdentical;
        }

        $processGreaterOrSmaller = $this->processGreaterOrSmaller($parent, $node, $expr);
        if ($processGreaterOrSmaller !== null) {
            return $processGreaterOrSmaller;
        }

        return $this->processMarkTruthy($parent, $node, $expr);
    }

    private function processIdentical(Node $node, FuncCall $funcCall, Expr $expr): ?Expr
    {
        if ($node instanceof Identical && $node->right instanceof LNumber && $node->right->value === 0) {
            $this->removeNode($funcCall);
            $node->right = new Array_([]);

            return $expr;
        }

        if ($node instanceof Identical && $node->left instanceof LNumber && $node->left->value === 0) {
            $this->removeNode($funcCall);
            $node->left = new Array_([]);

            return $expr;
        }

        return null;
    }

    private function processGreaterOrSmaller(Node $node, FuncCall $funcCall, Expr $expr): ?NotIdentical
    {
        if ($node instanceof Greater && $node->right instanceof LNumber && $node->right->value === 0) {
            $this->removeNode($funcCall);
            $this->removeNode($node->right);

            return new NotIdentical($expr, new Array_([]));
        }

        if ($node instanceof Smaller && $node->left instanceof LNumber && $node->left->value === 0) {
            $this->removeNode($funcCall);
            $this->removeNode($node->left);

            return new NotIdentical(new Array_([]), $expr);
        }

        return null;
    }

    private function processMarkTruthy(Node $node, FuncCall $funcCall, Expr $expr): ?NotIdentical
    {
        if (($node instanceof If_ || $node instanceof ElseIf_) && $node->cond === $funcCall) {
            $node->cond = new NotIdentical($expr, new Array_([]));
            return $node->cond;
        }

        return null;
    }
}
