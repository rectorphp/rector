<?php

declare (strict_types=1);
namespace Rector\DowngradePhp55\Rector\Isset_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220531\Webmozart\Assert\Assert;
/**
 * @changelog https://wiki.php.net/rfc/empty_isset_exprs
 *
 * @see Rector\Tests\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector\DowngradeArbitraryExpressionArgsToEmptyAndIssetRectorTest
 */
final class DowngradeArbitraryExpressionArgsToEmptyAndIssetRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade arbitrary expression arguments to empty() and isset()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
if (isset(some_function())) {
    // ...
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if (some_function() !== null) {
    // ...
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Isset_::class, \PhpParser\Node\Expr\Empty_::class];
    }
    /**
     * @param Isset_|Empty_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        return $node instanceof \PhpParser\Node\Expr\Empty_ ? $this->refactorEmpty($node) : $this->refactorIsset($node);
    }
    /**
     * @param \PhpParser\Node\Expr\Isset_|\PhpParser\Node\Expr\Empty_ $node
     */
    private function shouldSkip($node) : bool
    {
        if ($node instanceof \PhpParser\Node\Expr\Empty_) {
            return $this->isAcceptable($node->expr);
        }
        foreach ($node->vars as $var) {
            if (!$this->isAcceptable($var)) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * Check whether an expression can be passed to empty/isset before PHP 5.5
     */
    private function isAcceptable(\PhpParser\Node\Expr $expr) : bool
    {
        if ($expr instanceof \PhpParser\Node\Expr\Variable) {
            return \true;
        }
        if ($expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \true;
        }
        if ($expr instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
            return \true;
        }
        return $expr instanceof \PhpParser\Node\Expr\ArrayDimFetch;
    }
    private function refactorEmpty(\PhpParser\Node\Expr\Empty_ $empty) : \PhpParser\Node\Expr\BooleanNot
    {
        return new \PhpParser\Node\Expr\BooleanNot($empty->expr);
    }
    private function refactorIsset(\PhpParser\Node\Expr\Isset_ $isset) : \PhpParser\Node\Expr
    {
        $exprs = [];
        $currentExpr = null;
        foreach ($isset->vars as $var) {
            if (!$this->isAcceptable($var)) {
                $currentExpr = new \PhpParser\Node\Expr\BinaryOp\NotIdentical($var, $this->nodeFactory->createNull());
                $exprs[] = $currentExpr;
                continue;
            }
            if (!$currentExpr instanceof \PhpParser\Node\Expr\Isset_) {
                $currentExpr = new \PhpParser\Node\Expr\Isset_([]);
                $exprs[] = $currentExpr;
            }
            $currentExpr->vars[] = $var;
        }
        \RectorPrefix20220531\Webmozart\Assert\Assert::minCount($exprs, 1);
        return $this->joinWithBooleanAnd($exprs);
    }
    /**
     * @param non-empty-array<int, Expr> $exprs
     */
    private function joinWithBooleanAnd(array $exprs) : \PhpParser\Node\Expr
    {
        $expr = $exprs[0];
        $nbExprs = \count($exprs);
        for ($i = 1; $i < $nbExprs; ++$i) {
            $expr = new \PhpParser\Node\Expr\BinaryOp\BooleanAnd($expr, $exprs[$i]);
        }
        return $expr;
    }
}
