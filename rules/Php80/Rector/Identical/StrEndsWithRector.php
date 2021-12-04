<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Nette\NodeAnalyzer\BinaryOpAnalyzer;
use Rector\Nette\ValueObject\FuncCallAndExpr;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/add_str_starts_with_and_ends_with_functions
 *
 * @see \Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\StrEndsWithRectorTest
 */
final class StrEndsWithRector extends AbstractRector implements MinPhpVersionInterface
{
    public function __construct(
        private readonly BinaryOpAnalyzer $binaryOpAnalyzer,
        private readonly ArgsAnalyzer $argsAnalyzer
    ) {
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::STR_ENDS_WITH;
    }

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

        $isNotMatch = substr($haystack, -strlen($needle)) !== $needle;
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

        $isNotMatch = !str_ends_with($haystack, $needle);
    }
}
CODE_SAMPLE
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $isMatch = substr($haystack, -9) === 'hardcoded;

        $isNotMatch = substr($haystack, -9) !== 'hardcoded';
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $isMatch = str_ends_with($haystack, 'hardcoded');

        $isNotMatch = !str_ends_with($haystack, 'hardcoded');
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
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
     * $isMatch = 'needle' === substr($haystack, -6)
     */
    private function refactorSubstr(BinaryOp $binaryOp): FuncCall | BooleanNot | null
    {
        if ($binaryOp->left instanceof FuncCall && $this->isName($binaryOp->left, 'substr')) {
            $substrFuncCall = $binaryOp->left;
            $comparedNeedleExpr = $binaryOp->right;
        } elseif ($binaryOp->right instanceof FuncCall && $this->isName($binaryOp->right, 'substr')) {
            $substrFuncCall = $binaryOp->right;
            $comparedNeedleExpr = $binaryOp->left;
        } else {
            return null;
        }

        if (! $this->argsAnalyzer->isArgsInstanceInArgsPositions($substrFuncCall->args, [0, 1])) {
            return null;
        }

        /** @var Arg $secondArg */
        $secondArg = $substrFuncCall->args[1];
        if (! $this->isUnaryMinusStrlenFuncCallArgValue($secondArg->value, $comparedNeedleExpr) &&
            ! $this->isHardCodedLNumberAndString($secondArg->value, $comparedNeedleExpr)
        ) {
            return null;
        }

        /** @var Arg $firstArg */
        $firstArg = $substrFuncCall->args[0];
        $haystack = $firstArg->value;
        $isPositive = $binaryOp instanceof Identical;

        return $this->buildReturnNode($haystack, $comparedNeedleExpr, $isPositive);
    }

    private function refactorSubstrCompare(BinaryOp $binaryOp): FuncCall | BooleanNot | null
    {
        $funcCallAndExpr = $this->binaryOpAnalyzer->matchFuncCallAndOtherExpr($binaryOp, 'substr_compare');
        if (! $funcCallAndExpr instanceof FuncCallAndExpr) {
            return null;
        }

        $expr = $funcCallAndExpr->getExpr();
        if (! $this->valueResolver->isValue($expr, 0)) {
            return null;
        }

        $substrCompareFuncCall = $funcCallAndExpr->getFuncCall();
        if (! $this->argsAnalyzer->isArgsInstanceInArgsPositions($substrCompareFuncCall->args, [0, 1, 2])) {
            return null;
        }

        /** @var Arg $firstArg */
        $firstArg = $substrCompareFuncCall->args[0];
        $haystack = $firstArg->value;

        /** @var Arg $secondArg */
        $secondArg = $substrCompareFuncCall->args[1];
        $needle = $secondArg->value;

        /** @var Arg $thirdArg */
        $thirdArg = $substrCompareFuncCall->args[2];
        $thirdArgValue = $thirdArg->value;

        if (! $this->isUnaryMinusStrlenFuncCallArgValue($thirdArgValue, $needle) &&
            ! $this->isHardCodedLNumberAndString($thirdArgValue, $needle)
        ) {
            return null;
        }

        $isPositive = $binaryOp instanceof Identical;

        return $this->buildReturnNode($haystack, $needle, $isPositive);
    }

    private function isUnaryMinusStrlenFuncCallArgValue(Expr $substrOffset, Expr $needle): bool
    {
        if (! $substrOffset instanceof UnaryMinus) {
            return false;
        }

        if (! $substrOffset->expr instanceof FuncCall) {
            return false;
        }

        $funcCall = $substrOffset->expr;

        if (! $this->nodeNameResolver->isName($funcCall, 'strlen')) {
            return false;
        }

        if (! isset($funcCall->args[0])) {
            return false;
        }

        if (! $funcCall->args[0] instanceof Arg) {
            return false;
        }

        return $this->nodeComparator->areNodesEqual($funcCall->args[0]->value, $needle);
    }

    private function isHardCodedLNumberAndString(Expr $substrOffset, Expr $needle): bool
    {
        if (! $substrOffset instanceof UnaryMinus) {
            return false;
        }

        if (! $substrOffset->expr instanceof LNumber) {
            return false;
        }

        $lNumber = $substrOffset->expr;

        if (! $needle instanceof String_) {
            return false;
        }

        return $lNumber->value === strlen($needle->value);
    }

    private function buildReturnNode(Expr $haystack, Expr $needle, bool $isPositive): FuncCall | BooleanNot
    {
        $funcCall = $this->nodeFactory->createFuncCall('str_ends_with', [$haystack, $needle]);

        if (! $isPositive) {
            return new BooleanNot($funcCall);
        }

        return $funcCall;
    }
}
