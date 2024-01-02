<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Ternary;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\Ternary\TernaryToNullCoalescingRector\TernaryToNullCoalescingRectorTest
 */
final class TernaryToNullCoalescingRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes unneeded null check to ?? operator', [new CodeSample('$value === null ? 10 : $value;', '$value ?? 10;'), new CodeSample('isset($value) ? $value : 10;', '$value ?? 10;')]);
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
        if ($node->cond instanceof Isset_) {
            return $this->processTernaryWithIsset($node, $node->cond);
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
        if (!$checkedNode instanceof Expr) {
            return null;
        }
        if (!$fallbackNode instanceof Expr) {
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
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULL_COALESCE;
    }
    private function processTernaryWithIsset(Ternary $ternary, Isset_ $isset) : ?Coalesce
    {
        if (!$ternary->if instanceof Expr) {
            return null;
        }
        if ($isset->vars === null) {
            return null;
        }
        // none or multiple isset values cannot be handled here
        if (\count($isset->vars) > 1) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($ternary->if, $isset->vars[0])) {
            return null;
        }
        return new Coalesce($ternary->if, $ternary->else);
    }
    private function isNullMatch(Expr $possibleNullExpr, Expr $firstNode, Expr $secondNode) : bool
    {
        if (!$this->valueResolver->isNull($possibleNullExpr)) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($firstNode, $secondNode);
    }
}
