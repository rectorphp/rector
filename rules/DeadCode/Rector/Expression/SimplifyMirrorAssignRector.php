<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitor;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Expression\SimplifyMirrorAssignRector\SimplifyMirrorAssignRectorTest
 */
final class SimplifyMirrorAssignRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unneeded `$value = $value` assigns', [new CodeSample(<<<'CODE_SAMPLE'
function run() {
    $result = $result;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run() {
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     */
    public function refactor(Node $node) : ?int
    {
        if (!$node->expr instanceof Assign) {
            return null;
        }
        $assign = $node->expr;
        if (!$this->nodeComparator->areNodesEqual($assign->var, $assign->expr)) {
            return null;
        }
        return NodeVisitor::REMOVE_NODE;
    }
}
