<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
final class SafeLeftTypeBooleanAndOrAnalyzer
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, ExprAnalyzer $exprAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BinaryOp\BooleanOr $booleanAnd
     */
    public function isSafe($booleanAnd) : bool
    {
        $hasNonTypedFromParam = (bool) $this->betterNodeFinder->findFirst($booleanAnd->left, function (Node $node) : bool {
            return $node instanceof Variable && $this->exprAnalyzer->isNonTypedFromParam($node);
        });
        if ($hasNonTypedFromParam) {
            return \false;
        }
        // get type from Property and ArrayDimFetch is unreliable
        return !(bool) $this->betterNodeFinder->findFirst($booleanAnd->left, static function (Node $node) : bool {
            return $node instanceof PropertyFetch || $node instanceof StaticPropertyFetch || $node instanceof ArrayDimFetch;
        });
    }
}
