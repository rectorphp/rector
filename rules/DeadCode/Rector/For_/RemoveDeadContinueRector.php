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
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\For_\RemoveDeadContinueRector\RemoveDeadContinueRectorTest
 */
final class RemoveDeadContinueRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove useless continue at the end of loops', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Do_::class, \PhpParser\Node\Stmt\For_::class, \PhpParser\Node\Stmt\Foreach_::class, \PhpParser\Node\Stmt\While_::class];
    }
    /**
     * @param Do_|For_|Foreach_|While_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
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
        $lastStmt = $stmts[$lastKey];
        return $this->isRemovable($lastStmt);
    }
    private function isRemovable(\PhpParser\Node\Stmt $stmt) : bool
    {
        if (!$stmt instanceof \PhpParser\Node\Stmt\Continue_) {
            return \false;
        }
        if ($stmt->num instanceof \PhpParser\Node\Scalar\LNumber) {
            return $stmt->num->value < 2;
        }
        return \true;
    }
}
