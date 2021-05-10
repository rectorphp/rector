<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector\RemoveDeadConditionAboveReturnRectorTest
 */
final class RemoveDeadConditionAboveReturnRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove dead condition above return', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function go()
    {
        if (1 === 1) {
            return 'yes';
        }

        return 'yes';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function go()
    {
        return 'yes';
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
        return [\PhpParser\Node\Stmt\Return_::class];
    }
    /**
     * @param Return_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $previousNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
        if (!$previousNode instanceof \PhpParser\Node\Stmt\If_) {
            return null;
        }
        if ($previousNode->elseifs !== []) {
            return null;
        }
        if ($previousNode->else instanceof \PhpParser\Node\Stmt\Else_) {
            return null;
        }
        $countStmt = \count($previousNode->stmts);
        if ($countStmt === 0) {
            $this->removeNode($previousNode);
            return $node;
        }
        if ($countStmt > 1) {
            return null;
        }
        $stmt = $previousNode->stmts[0];
        if (!$stmt instanceof \PhpParser\Node\Stmt\Return_) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($stmt, $node)) {
            return null;
        }
        $this->removeNode($previousNode);
        return $node;
    }
}
