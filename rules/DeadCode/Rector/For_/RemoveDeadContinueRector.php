<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\For_;

use PhpParser\Node;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\While_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\For_\RemoveDeadContinueRector\RemoveDeadContinueRectorTest
 */
final class RemoveDeadContinueRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove useless continue at the end of loops', [new CodeSample(<<<'CODE_SAMPLE'
while ($i < 10) {
    ++$i;
    continue;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
while ($i < 10) {
    ++$i;
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
        $modified = \false;
        while ($this->canRemoveLastStatement($node->stmts)) {
            \array_pop($node->stmts);
            $modified = \true;
        }
        return $modified ? $node : null;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function canRemoveLastStatement(array $stmts) : bool
    {
        if ($stmts === []) {
            return \false;
        }
        \end($stmts);
        $lastKey = \key($stmts);
        \reset($stmts);
        $lastStmt = $stmts[$lastKey];
        return $this->isRemovable($lastStmt);
    }
    private function isRemovable(Stmt $stmt) : bool
    {
        if (!$stmt instanceof Continue_) {
            return \false;
        }
        if ($stmt->num instanceof LNumber) {
            return $stmt->num->value < 2;
        }
        return \true;
    }
}
