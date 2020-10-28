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
use PhpParser\Node\Expr\BooleanNot;
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
if (count($array)) {

}
if (! count($array)) {

}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
$array === [];
$array !== [];
if ($array !== []) {

}
if ($array === []) {

}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, If_::class, ElseIf_::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isConditional($node)) {
            return $this->processMarkTruthyNegationInsideConditional($node);
        }

        $functionName = $this->getName($node);
        if ($functionName !== 'count') {
            return null;
        }

        /** @var Expr $expr */
        $expr = $node->args[0]->value;

        // not pass array type, skip
        if (! $this->isArray($expr)) {
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

        return $this->processMarkTruthyAndTruthyNegation($parent, $node, $expr);
    }

    private function isConditional(?Node $node): bool
    {
        return $node instanceof If_ || $node instanceof ElseIf_;
    }

    private function processMarkTruthyNegationInsideConditional(Node $node): ?Node
    {
        if (! $node->cond instanceof BooleanNot || ! $node->cond->expr instanceof FuncCall || $this->getName(
            $node->cond->expr
        ) !== 'count') {
            return null;
        }

        /** @var Expr $expr */
        $expr = $node->cond->expr->args[0]->value;

        // not pass array type, skip
        if (! $this->isArray($expr)) {
            return null;
        }

        $node->cond = new Identical($expr, new Array_([]));
        return $node;
    }

    private function isArray(Expr $expr): bool
    {
        /** @var Scope|null $scope */
        $scope = $expr->getAttribute(AttributeKey::SCOPE);

        if (! $scope instanceof Scope) {
            return false;
        }

        return $scope->getType($expr) instanceof ArrayType;
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

    private function processMarkTruthyAndTruthyNegation(Node $node, FuncCall $funcCall, Expr $expr): ?Expr
    {
        if ($this->isConditional($node) && $node->cond === $funcCall) {
            $node->cond = new NotIdentical($expr, new Array_([]));
            return $node->cond;
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($node instanceof BooleanNot && $node->expr === $funcCall && ! $this->isConditional($parent)) {
            $identical = new Identical($expr, new Array_([]));
            $this->addNodeBeforeNode($identical, $node);
            $this->removeNode($node);

            return $identical;
        }

        return null;
    }
}
