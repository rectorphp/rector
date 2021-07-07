<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\NodeAnalyzer\BinaryOpAnalyzer;
use Rector\Nette\ValueObject\FuncCallAndExpr;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/add_str_starts_with_and_ends_with_functions
 *
 * @see \Rector\Tests\Php80\Rector\Identical\StrEndsWithRector\StrEndsWithRectorTest
 */
final class StrEndsWithRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Nette\NodeAnalyzer\BinaryOpAnalyzer
     */
    private $binaryOpAnalyzer;
    public function __construct(\Rector\Nette\NodeAnalyzer\BinaryOpAnalyzer $binaryOpAnalyzer)
    {
        $this->binaryOpAnalyzer = $binaryOpAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change helper functions to str_ends_with()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
), new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\BinaryOp\Identical::class, \PhpParser\Node\Expr\BinaryOp\NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        return $this->refactorSubstr($node) ?? $this->refactorSubstrCompare($node);
    }
    /**
     * Covers:
     * $isMatch = substr($haystack, -strlen($needle)) === $needle;
     * $isMatch = 'needle' === substr($haystack, -6)
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\BooleanNot|null
     */
    private function refactorSubstr(\PhpParser\Node\Expr\BinaryOp $binaryOp)
    {
        if ($binaryOp->left instanceof \PhpParser\Node\Expr\FuncCall && $this->isName($binaryOp->left, 'substr')) {
            $substrFuncCall = $binaryOp->left;
            $comparedNeedleExpr = $binaryOp->right;
        } elseif ($binaryOp->right instanceof \PhpParser\Node\Expr\FuncCall && $this->isName($binaryOp->right, 'substr')) {
            $substrFuncCall = $binaryOp->right;
            $comparedNeedleExpr = $binaryOp->left;
        } else {
            return null;
        }
        $haystack = $substrFuncCall->args[0]->value;
        if (!$this->isUnaryMinusStrlenFuncCallArgValue($substrFuncCall->args[1]->value, $comparedNeedleExpr) && !$this->isHardCodedLNumberAndString($substrFuncCall->args[1]->value, $comparedNeedleExpr)) {
            return null;
        }
        $isPositive = $binaryOp instanceof \PhpParser\Node\Expr\BinaryOp\Identical;
        return $this->buildReturnNode($haystack, $comparedNeedleExpr, $isPositive);
    }
    /**
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\BooleanNot|null
     */
    private function refactorSubstrCompare(\PhpParser\Node\Expr\BinaryOp $binaryOp)
    {
        $funcCallAndExpr = $this->binaryOpAnalyzer->matchFuncCallAndOtherExpr($binaryOp, 'substr_compare');
        if (!$funcCallAndExpr instanceof \Rector\Nette\ValueObject\FuncCallAndExpr) {
            return null;
        }
        $expr = $funcCallAndExpr->getExpr();
        if (!$this->valueResolver->isValue($expr, 0)) {
            return null;
        }
        $substrCompareFuncCall = $funcCallAndExpr->getFuncCall();
        $haystack = $substrCompareFuncCall->args[0]->value;
        $needle = $substrCompareFuncCall->args[1]->value;
        if (!$this->isUnaryMinusStrlenFuncCallArgValue($substrCompareFuncCall->args[2]->value, $needle) && !$this->isHardCodedLNumberAndString($substrCompareFuncCall->args[2]->value, $needle)) {
            return null;
        }
        $isPositive = $binaryOp instanceof \PhpParser\Node\Expr\BinaryOp\Identical;
        return $this->buildReturnNode($haystack, $needle, $isPositive);
    }
    private function isUnaryMinusStrlenFuncCallArgValue(\PhpParser\Node $substrOffset, \PhpParser\Node $needle) : bool
    {
        if (!$substrOffset instanceof \PhpParser\Node\Expr\UnaryMinus) {
            return \false;
        }
        if (!$substrOffset->expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return \false;
        }
        $funcCall = $substrOffset->expr;
        if (!$this->nodeNameResolver->isName($funcCall, 'strlen')) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($funcCall->args[0]->value, $needle);
    }
    private function isHardCodedLNumberAndString(\PhpParser\Node $substrOffset, \PhpParser\Node $needle) : bool
    {
        if (!$substrOffset instanceof \PhpParser\Node\Expr\UnaryMinus) {
            return \false;
        }
        if (!$substrOffset->expr instanceof \PhpParser\Node\Scalar\LNumber) {
            return \false;
        }
        $lNumber = $substrOffset->expr;
        if (!$needle instanceof \PhpParser\Node\Scalar\String_) {
            return \false;
        }
        return $lNumber->value === \strlen($needle->value);
    }
    /**
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\BooleanNot
     */
    private function buildReturnNode(?\PhpParser\Node\Expr $haystack, ?\PhpParser\Node\Expr $needle, bool $isPositive)
    {
        $funcCall = $this->nodeFactory->createFuncCall('str_ends_with', [$haystack, $needle]);
        if (!$isPositive) {
            return new \PhpParser\Node\Expr\BooleanNot($funcCall);
        }
        return $funcCall;
    }
}
