<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Assign\SplitDoubleAssignRector\SplitDoubleAssignRectorTest
 */
final class SplitDoubleAssignRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Split multiple inline assigns to each own lines default value, to prevent undefined array issues', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $one = $two = 1;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $one = 1;
        $two = 1;
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
        return [\PhpParser\Node\Expr\Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        if (!$node->expr instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $newAssign = new \PhpParser\Node\Expr\Assign($node->var, $node->expr->expr);
        if (!$node->expr->expr instanceof \PhpParser\Node\Expr\CallLike) {
            $this->nodesToAddCollector->addNodeAfterNode($node->expr, $node);
            return $newAssign;
        }
        $varAssign = new \PhpParser\Node\Expr\Assign($node->expr->var, $node->var);
        $this->nodesToAddCollector->addNodeBeforeNode(new \PhpParser\Node\Stmt\Expression($newAssign), $node);
        return $varAssign;
    }
}
