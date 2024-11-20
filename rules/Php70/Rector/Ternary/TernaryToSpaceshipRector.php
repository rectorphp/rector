<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\Spaceship;
use PhpParser\Node\Expr\Ternary;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\Ternary\TernaryToSpaceshipRector\TernaryToSpaceshipRectorTest
 */
final class TernaryToSpaceshipRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use <=> spaceship instead of ternary with same effect', [new CodeSample(<<<'CODE_SAMPLE'
function order_func($a, $b) {
    return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function order_func($a, $b) {
    return $a <=> $b;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var Ternary $nestedTernary */
        $nestedTernary = $node->else;
        $spaceshipNode = $this->processSmallerThanTernary($node, $nestedTernary);
        if ($spaceshipNode instanceof Spaceship) {
            return $spaceshipNode;
        }
        return $this->processGreaterThanTernary($node, $nestedTernary);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SPACESHIP;
    }
    private function shouldSkip(Ternary $ternary) : bool
    {
        if (!$ternary->cond instanceof BinaryOp) {
            return \true;
        }
        if (!$ternary->else instanceof Ternary) {
            return \true;
        }
        $nestedTernary = $ternary->else;
        if (!$nestedTernary->cond instanceof BinaryOp) {
            return \true;
        }
        // $a X $b ? . : ($a X $b ? . : .)
        if (!$this->nodeComparator->areNodesEqual($ternary->cond->left, $nestedTernary->cond->left)) {
            return \true;
        }
        // $a X $b ? . : ($a X $b ? . : .)
        return !$this->nodeComparator->areNodesEqual($ternary->cond->right, $nestedTernary->cond->right);
    }
    /**
     * Matches "$a < $b ? -1 : ($a > $b ? 1 : 0)"
     */
    private function processSmallerThanTernary(Ternary $node, Ternary $nestedTernary) : ?Spaceship
    {
        if (!$node->cond instanceof Smaller) {
            return null;
        }
        if (!$nestedTernary->cond instanceof Greater) {
            return null;
        }
        if (!$this->valueResolver->areValuesEqual([$node->if, $nestedTernary->if, $nestedTernary->else], [-1, 1, 0])) {
            return null;
        }
        return new Spaceship($node->cond->left, $node->cond->right);
    }
    /**
     * Matches "$a > $b ? -1 : ($a < $b ? 1 : 0)"
     */
    private function processGreaterThanTernary(Ternary $node, Ternary $nestedTernary) : ?Spaceship
    {
        if (!$node->cond instanceof Greater) {
            return null;
        }
        if (!$nestedTernary->cond instanceof Smaller) {
            return null;
        }
        if (!$this->valueResolver->areValuesEqual([$node->if, $nestedTernary->if, $nestedTernary->else], [-1, 1, 0])) {
            return null;
        }
        return new Spaceship($node->cond->right, $node->cond->left);
    }
}
