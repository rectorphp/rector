<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Ternary;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Php70\Tests\Rector\Ternary\TernaryToNullCoalescingRector\TernaryToNullCoalescingRectorTest
 */
final class TernaryToNullCoalescingRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes unneeded null check to ?? operator',
            [
                new CodeSample('$value === null ? 10 : $value;', '$value ?? 10;'),
                new CodeSample('isset($value) ? $value : 10;', '$value ?? 10;'),
            ]
        );
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
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::NULL_COALESCE)) {
            return null;
        }

        if ($node->cond instanceof Isset_) {
            return $this->processTernaryWithIsset($node);
        }

        if ($node->cond instanceof Identical) {
            $checkedNode = $node->else;
            $fallbackNode = $node->if;
        } elseif ($node->cond instanceof NotIdentical) {
            $checkedNode = $node->if;
            $fallbackNode = $node->else;
        } else {
            // not a match
            return null;
        }
        if ($checkedNode === null) {
            return null;
        }
        if (! $fallbackNode instanceof Expr) {
            return null;
        }

        /** @var Identical|NotIdentical $ternaryCompareNode */
        $ternaryCompareNode = $node->cond;
        if ($this->isNullMatch($ternaryCompareNode->left, $ternaryCompareNode->right, $checkedNode)) {
            return new Coalesce($checkedNode, $fallbackNode);
        }
        if ($this->isNullMatch($ternaryCompareNode->right, $ternaryCompareNode->left, $checkedNode)) {
            return new Coalesce($checkedNode, $fallbackNode);
        }

        return null;
    }

    private function processTernaryWithIsset(Ternary $ternary): ?Coalesce
    {
        if ($ternary->if === null) {
            return null;
        }

        /** @var Isset_ $issetNode */
        $issetNode = $ternary->cond;
        // none or multiple isset values cannot be handled here
        if (! isset($issetNode->vars[0])) {
            return null;
        }
        if (count($issetNode->vars) > 1) {
            return null;
        }

        if ($this->areNodesEqual($ternary->if, $issetNode->vars[0])) {
            return new Coalesce($ternary->if, $ternary->else);
        }

        return null;
    }

    private function isNullMatch(Expr $possibleNullExpr, Node $firstNode, Node $secondNode): bool
    {
        if (! $this->valueResolver->isNull($possibleNullExpr)) {
            return false;
        }

        return $this->areNodesEqual($firstNode, $secondNode);
    }
}
