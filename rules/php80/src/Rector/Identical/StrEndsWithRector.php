<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\UnaryMinus;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/add_str_starts_with_and_ends_with_functions
 *
 * @see \Rector\Php80\Tests\Rector\Identical\StrEndsWithRector\StrEndsWithRectorTest
 */
final class StrEndsWithRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change helper functions to str_ends_with()', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $isMatch = substr($haystack, -strlen($needle)) === $needle;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $isMatch = str_ends_with($haystack, $needle);
    }
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
        return [Identical::class, NotIdentical::class];
    }

    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->refactorSubstr($node) ?? $this->refactorSubstrCompare($node);
    }

    /**
     * Covers:
     * $isMatch = substr($haystack, -strlen($needle)) === $needle;
     */
    private function refactorSubstr(BinaryOp $binaryOp): ?FuncCall
    {
        if ($this->isFuncCallName($binaryOp->left, 'substr')) {
            $substrFuncCall = $binaryOp->left;
            $comparedNeedleExpr = $binaryOp->right;
        } elseif ($this->isFuncCallName($binaryOp->right, 'substr')) {
            $substrFuncCall = $binaryOp->right;
            $comparedNeedleExpr = $binaryOp->left;
        } else {
            return null;
        }

        $haystack = $substrFuncCall->args[0]->value;

        $needle = $this->matchUnaryMinusStrlenFuncCallArgValue($substrFuncCall->args[1]->value);
        if (! $this->areNodesEqual($needle, $comparedNeedleExpr)) {
            return null;
        }

        return $this->nodeFactory->createFuncCall('str_ends_with', [$haystack, $needle]);
    }

    private function refactorSubstrCompare(BinaryOp $binaryOp): ?FuncCall
    {
        if ($this->isFuncCallName($binaryOp->left, 'substr_compare')) {
            $substrCompareFuncCall = $binaryOp->left;
            if (! $this->valueResolver->isValue($binaryOp->right, 0)) {
                return null;
            }
        } elseif ($this->isFuncCallName($binaryOp->right, 'substr_compare')) {
            $substrCompareFuncCall = $binaryOp->right;
            if (! $this->valueResolver->isValue($binaryOp->left, 0)) {
                return null;
            }
        } else {
            return null;
        }

        $haystack = $substrCompareFuncCall->args[0]->value;
        $needle = $substrCompareFuncCall->args[1]->value;

        $comparedNeedleExpr = $this->matchUnaryMinusStrlenFuncCallArgValue($substrCompareFuncCall->args[2]->value);
        if (! $this->areNodesEqual($needle, $comparedNeedleExpr)) {
            return null;
        }

        return $this->nodeFactory->createFuncCall('str_ends_with', [$haystack, $needle]);
    }

    private function matchUnaryMinusStrlenFuncCallArgValue(Node $node): ?Expr
    {
        if (! $node instanceof UnaryMinus) {
            return null;
        }

        if (! $this->isFuncCallName($node->expr, 'strlen')) {
            return null;
        }

        /** @var FuncCall $funcCall */
        $funcCall = $node->expr;

        return $funcCall->args[0]->value;
    }
}
