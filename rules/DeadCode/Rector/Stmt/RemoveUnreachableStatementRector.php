<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DeadCode\Rector\Stmt;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Exit_;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PhpParser\Node\Stmt\Break_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Continue_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Finally_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Goto_;
use RectorPrefix20220606\PhpParser\Node\Stmt\InlineHTML;
use RectorPrefix20220606\PhpParser\Node\Stmt\Label;
use RectorPrefix20220606\PhpParser\Node\Stmt\Nop;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Throw_;
use RectorPrefix20220606\PhpParser\Node\Stmt\TryCatch;
use RectorPrefix20220606\Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/phpstan/phpstan/blob/83078fe308a383c618b8c1caec299e5765d9ac82/src/Node/UnreachableStatementNode.php
 *
 * @see \Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\RemoveUnreachableStatementRectorTest
 */
final class RemoveUnreachableStatementRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unreachable statements', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 5;

        $removeMe = 10;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 5;
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $originalStmts = $node->stmts;
        $cleanedStmts = $this->processCleanUpUnreachabelStmts($node->stmts);
        if ($cleanedStmts === $originalStmts) {
            return null;
        }
        $node->stmts = $cleanedStmts;
        return $node;
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function processCleanUpUnreachabelStmts(array $stmts) : array
    {
        foreach ($stmts as $key => $stmt) {
            if (!isset($stmts[$key - 1])) {
                continue;
            }
            if ($stmt instanceof Nop) {
                continue;
            }
            $previousStmt = $stmts[$key - 1];
            // unset...
            if ($this->shouldRemove($previousStmt, $stmt)) {
                \array_splice($stmts, $key);
                return $stmts;
            }
        }
        return $stmts;
    }
    private function shouldRemove(Stmt $previousStmt, Stmt $currentStmt) : bool
    {
        if ($currentStmt instanceof InlineHTML) {
            return \false;
        }
        if ($previousStmt instanceof Throw_) {
            return \true;
        }
        if ($previousStmt instanceof Expression && $previousStmt->expr instanceof Exit_) {
            return \true;
        }
        if ($previousStmt instanceof Goto_ && $currentStmt instanceof Label) {
            return \false;
        }
        if (\in_array(\get_class($previousStmt), [Return_::class, Break_::class, Continue_::class, Goto_::class], \true)) {
            return \true;
        }
        if (!$previousStmt instanceof TryCatch) {
            return \false;
        }
        $isUnreachable = $currentStmt->getAttribute(AttributeKey::IS_UNREACHABLE);
        if ($isUnreachable !== \true) {
            return \false;
        }
        if (!$previousStmt->finally instanceof Finally_) {
            return \false;
        }
        return $this->cleanNop($previousStmt->finally->stmts) !== [];
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function cleanNop(array $stmts) : array
    {
        return \array_filter($stmts, function (Stmt $stmt) : bool {
            return !$stmt instanceof Nop;
        });
    }
}
