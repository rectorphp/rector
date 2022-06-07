<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\For_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\For_\RemoveDeadLoopRector\RemoveDeadLoopRectorTest
 */
final class RemoveDeadLoopRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove loop with no body', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($values)
    {
        for ($i=1; $i<count($values); ++$i) {
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($values)
    {
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
        return [Do_::class, For_::class, Foreach_::class, While_::class];
    }
    /**
     * @param Do_|For_|Foreach_|While_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts !== []) {
            return null;
        }
        $this->removeNode($node);
        return $node;
    }
}
