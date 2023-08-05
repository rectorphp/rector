<?php

declare (strict_types=1);
namespace Rector\Instanceof_\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Ternary;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Instanceof_\Rector\Ternary\FlipNegatedTernaryInstanceofRector\FlipNegatedTernaryInstanceofRectorTest
 */
final class FlipNegatedTernaryInstanceofRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Flip negated ternary of instanceof to direct use of object', [new CodeSample('echo ! $object instanceof Product ? null : $object->getPrice();', 'echo $object instanceof Product ? $object->getPrice() : null;')]);
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
        if (!$node->if instanceof Expr) {
            return null;
        }
        if (!$node->cond instanceof BooleanNot) {
            return null;
        }
        $booleanNot = $node->cond;
        if (!$booleanNot->expr instanceof Instanceof_) {
            return null;
        }
        $node->cond = $booleanNot->expr;
        // flip if and else
        [$node->if, $node->else] = [$node->else, $node->if];
        return $node;
    }
}
