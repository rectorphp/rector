<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\Spaceship;
use PhpParser\Node\Expr\Ternary;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/combined-comparison-operator
 * @see \Rector\Php70\Tests\Rector\Ternary\TernaryToSpaceshipRector\TernaryToSpaceshipRectorTest
 */
final class TernaryToSpaceshipRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Use <=> spaceship instead of ternary with same effect',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
function order_func($a, $b) {
    return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
function order_func($a, $b) {
    return $a <=> $b;
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
        return [Ternary::class];
    }

    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SPACESHIP)) {
            return null;
        }

        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var Ternary $nestedTernary */
        $nestedTernary = $node->else;

        $spaceshipNode = $this->processSmallerThanTernary($node, $nestedTernary);
        if ($spaceshipNode !== null) {
            return $spaceshipNode;
        }

        return $this->processGreaterThanTernary($node, $nestedTernary);
    }

    private function shouldSkip(Ternary $ternary): bool
    {
        if (! $ternary->cond instanceof BinaryOp) {
            return true;
        }

        if (! $ternary->else instanceof Ternary) {
            return true;
        }

        $nestedTernary = $ternary->else;

        if (! $nestedTernary->cond instanceof BinaryOp) {
            return true;
        }

        // $a X $b ? . : ($a X $b ? . : .)
        if (! $this->areNodesEqual($ternary->cond->left, $nestedTernary->cond->left)) {
            return true;
        }
        // $a X $b ? . : ($a X $b ? . : .)
        return ! $this->areNodesEqual($ternary->cond->right, $nestedTernary->cond->right);
    }

    /**
     * Matches "$a < $b ? -1 : ($a > $b ? 1 : 0)"
     */
    private function processSmallerThanTernary(Ternary $node, Ternary $nestedTernary): ?Spaceship
    {
        if ($node->cond instanceof Smaller && $nestedTernary->cond instanceof Greater && $this->areValues(
            [$node->if, $nestedTernary->if, $nestedTernary->else],
            [-1, 1, 0]
        )) {
            return new Spaceship($node->cond->left, $node->cond->right);
        }

        return null;
    }

    /**
     * Matches "$a > $b ? -1 : ($a < $b ? 1 : 0)"
     */
    private function processGreaterThanTernary(Ternary $node, Ternary $nestedTernary): ?Spaceship
    {
        if ($node->cond instanceof Greater && $nestedTernary->cond instanceof Smaller && $this->areValues(
            [$node->if, $nestedTernary->if, $nestedTernary->else],
            [-1, 1, 0]
        )) {
            return new Spaceship($node->cond->right, $node->cond->left);
        }

        return null;
    }
}
