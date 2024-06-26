<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\NodeAnalyzer\BinaryOpAnalyzer;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\FuncCallAndExpr;
use Rector\ValueObject\PhpVersionFeature;
use Rector\ValueObject\PolyfillPackage;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Rector\VersionBonding\Contract\RelatedPolyfillInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\StrEndsWithRectorTest
 */
final class StrEndsWithRector extends AbstractRector implements MinPhpVersionInterface, RelatedPolyfillInterface
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\BinaryOpAnalyzer
     */
    private $binaryOpAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(BinaryOpAnalyzer $binaryOpAnalyzer, ValueResolver $valueResolver)
    {
        $this->binaryOpAnalyzer = $binaryOpAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::STR_ENDS_WITH;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change helper functions to str_ends_with()', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $isMatch = substr($haystack, -strlen($needle)) === $needle;

        $isNotMatch = substr($haystack, -strlen($needle)) !== $needle;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $isMatch = str_ends_with($haystack, $needle);

        $isNotMatch = !str_ends_with($haystack, $needle);
    }
}
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $isMatch = substr($haystack, -9) === 'hardcoded;

        $isNotMatch = substr($haystack, -9) !== 'hardcoded';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $isMatch = str_ends_with($haystack, 'hardcoded');

        $isNotMatch = !str_ends_with($haystack, 'hardcoded');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Identical::class, NotIdentical::class, Equal::class, NotEqual::class];
    }
    /**
     * @param Identical|NotIdentical|Equal|NotEqual $node
     */
    public function refactor(Node $node) : ?Node
    {
        return $this->refactorSubstr($node) ?? $this->refactorSubstrCompare($node);
    }
    public function providePolyfillPackage() : string
    {
        return PolyfillPackage::PHP_80;
    }
    /**
     * Covers:
     * $isMatch = substr($haystack, -strlen($needle)) === $needle;
     * $isMatch = 'needle' === substr($haystack, -6)
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\BooleanNot|null
     */
    private function refactorSubstr(BinaryOp $binaryOp)
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
        if ($substrFuncCall->isFirstClassCallable()) {
            return null;
        }
        if (\count($substrFuncCall->getArgs()) < 2) {
            return null;
        }
        $needle = $substrFuncCall->getArgs()[1]->value;
        if (!$this->isUnaryMinusStrlenFuncCallArgValue($needle, $comparedNeedleExpr) && !$this->isHardCodedLNumberAndString($needle, $comparedNeedleExpr)) {
            return null;
        }
        $haystack = $substrFuncCall->getArgs()[0]->value;
        $isPositive = $binaryOp instanceof Identical || $binaryOp instanceof Equal;
        return $this->buildReturnNode($haystack, $comparedNeedleExpr, $isPositive);
    }
    /**
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\BooleanNot|null
     */
    private function refactorSubstrCompare(BinaryOp $binaryOp)
    {
        $funcCallAndExpr = $this->binaryOpAnalyzer->matchFuncCallAndOtherExpr($binaryOp, 'substr_compare');
        if (!$funcCallAndExpr instanceof FuncCallAndExpr) {
            return null;
        }
        $expr = $funcCallAndExpr->getExpr();
        if (!$this->valueResolver->isValue($expr, 0)) {
            return null;
        }
        $substrCompareFuncCall = $funcCallAndExpr->getFuncCall();
        $args = $substrCompareFuncCall->getArgs();
        if (\count($args) < 2) {
            return null;
        }
        $haystack = $args[0]->value;
        $needle = $args[1]->value;
        $thirdArgValue = $args[2]->value;
        $isCaseInsensitiveValue = isset($args[4]) ? $this->valueResolver->getValue($args[4]->value) : null;
        // is case insensitive → not valid replacement
        if ($isCaseInsensitiveValue === \true) {
            return null;
        }
        if (!$this->isUnaryMinusStrlenFuncCallArgValue($thirdArgValue, $needle) && !$this->isHardCodedLNumberAndString($thirdArgValue, $needle)) {
            return null;
        }
        $isPositive = $binaryOp instanceof Identical || $binaryOp instanceof Equal;
        return $this->buildReturnNode($haystack, $needle, $isPositive);
    }
    private function isUnaryMinusStrlenFuncCallArgValue(Expr $substrOffset, Expr $needle) : bool
    {
        if (!$substrOffset instanceof UnaryMinus) {
            return \false;
        }
        if (!$substrOffset->expr instanceof FuncCall) {
            return \false;
        }
        $funcCall = $substrOffset->expr;
        if (!$this->nodeNameResolver->isName($funcCall, 'strlen')) {
            return \false;
        }
        if (!isset($funcCall->getArgs()[0])) {
            return \false;
        }
        if (!$funcCall->args[0] instanceof Arg) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($funcCall->args[0]->value, $needle);
    }
    private function isHardCodedLNumberAndString(Expr $substrOffset, Expr $needle) : bool
    {
        if (!$substrOffset instanceof UnaryMinus) {
            return \false;
        }
        if (!$substrOffset->expr instanceof LNumber) {
            return \false;
        }
        $lNumber = $substrOffset->expr;
        if (!$needle instanceof String_) {
            return \false;
        }
        return $lNumber->value === \strlen($needle->value);
    }
    /**
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\BooleanNot
     */
    private function buildReturnNode(Expr $haystack, Expr $needle, bool $isPositive)
    {
        $funcCall = $this->nodeFactory->createFuncCall('str_ends_with', [$haystack, $needle]);
        if (!$isPositive) {
            return new BooleanNot($funcCall);
        }
        return $funcCall;
    }
}
