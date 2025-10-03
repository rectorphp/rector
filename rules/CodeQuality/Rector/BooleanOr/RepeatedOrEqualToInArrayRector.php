<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\BooleanOr;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\CodeQuality\ValueObject\ComparedExprAndValueExpr;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\BooleanOr\RepeatedOrEqualToInArrayRector\RepeatedOrEqualToInArrayRectorTest
 */
final class RepeatedOrEqualToInArrayRector extends AbstractRector
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Simplify repeated || compare of same value, to in_array() class', [new CodeSample(<<<'CODE_SAMPLE'
if ($value === 10 || $value === 20 || $value === 30) {
    // ...
}

CODE_SAMPLE
, <<<'CODE_SAMPLE'
if (in_array($value, [10, 20, 30], true)) {
    // ...
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [BooleanOr::class];
    }
    /**
     * @param BooleanOr $node
     */
    public function refactor(Node $node): ?FuncCall
    {
        if (!$this->isEqualOrIdentical($node->right)) {
            return null;
        }
        // match compared variable and expr
        if (!$node->left instanceof BooleanOr && !$this->isEqualOrIdentical($node->left)) {
            return null;
        }
        $comparedExprAndValueExprs = $this->matchComparedAndDesiredValues($node);
        if ($comparedExprAndValueExprs === null) {
            return null;
        }
        if (count($comparedExprAndValueExprs) < 3) {
            return null;
        }
        // ensure all compared expr are the same
        $valueExprs = $this->resolveValueExprs($comparedExprAndValueExprs);
        /** @var ComparedExprAndValueExpr $firstComparedExprAndValue */
        $firstComparedExprAndValue = array_pop($comparedExprAndValueExprs);
        // all compared expr must be equal
        foreach ($comparedExprAndValueExprs as $comparedExprAndValueExpr) {
            if (!$this->nodeComparator->areNodesEqual($firstComparedExprAndValue->getComparedExpr(), $comparedExprAndValueExpr->getComparedExpr())) {
                return null;
            }
        }
        $array = $this->nodeFactory->createArray($valueExprs);
        $args = $this->nodeFactory->createArgs([$firstComparedExprAndValue->getComparedExpr(), $array]);
        $identicals = $this->betterNodeFinder->findInstanceOf($node, Identical::class);
        $equals = $this->betterNodeFinder->findInstanceOf($node, Equal::class);
        if ($identicals !== []) {
            if ($equals !== []) {
                // mix identical and equals, keep as is
                // @see https://3v4l.org/24cFl
                return null;
            }
            $args[] = new Arg(new ConstFetch(new Name('true')));
        }
        return new FuncCall(new Name('in_array'), $args);
    }
    private function isEqualOrIdentical(Expr $expr): bool
    {
        if ($expr instanceof Identical) {
            return \true;
        }
        return $expr instanceof Equal;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\Equal $expr
     */
    private function matchComparedExprAndValueExpr($expr): ComparedExprAndValueExpr
    {
        return new ComparedExprAndValueExpr($expr->left, $expr->right);
    }
    /**
     * @param ComparedExprAndValueExpr[] $comparedExprAndValueExprs
     * @return Expr[]
     */
    private function resolveValueExprs(array $comparedExprAndValueExprs): array
    {
        $valueExprs = [];
        foreach ($comparedExprAndValueExprs as $comparedExprAndValueExpr) {
            $valueExprs[] = $comparedExprAndValueExpr->getValueExpr();
        }
        return $valueExprs;
    }
    /**
     * @return null|ComparedExprAndValueExpr[]
     */
    private function matchComparedAndDesiredValues(BooleanOr $booleanOr): ?array
    {
        /** @var Identical|Equal $rightCompare */
        $rightCompare = $booleanOr->right;
        // match compared expr and desired value
        $comparedExprAndValueExprs = [$this->matchComparedExprAndValueExpr($rightCompare)];
        $currentBooleanOr = $booleanOr;
        while ($currentBooleanOr->left instanceof BooleanOr) {
            if (!$this->isEqualOrIdentical($currentBooleanOr->left->right)) {
                return null;
            }
            /** @var Identical|Equal $leftRight */
            $leftRight = $currentBooleanOr->left->right;
            $comparedExprAndValueExprs[] = $this->matchComparedExprAndValueExpr($leftRight);
            $currentBooleanOr = $currentBooleanOr->left;
        }
        if (!$this->isEqualOrIdentical($currentBooleanOr->left)) {
            return null;
        }
        /** @var Identical|Equal $leftCompare */
        $leftCompare = $currentBooleanOr->left;
        $comparedExprAndValueExprs[] = $this->matchComparedExprAndValueExpr($leftCompare);
        // keep original natural order, as left/right goes from bottom up
        return array_reverse($comparedExprAndValueExprs);
    }
}
