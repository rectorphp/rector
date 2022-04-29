<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Goto_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Label;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\While_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/phpstan/phpstan/blob/83078fe308a383c618b8c1caec299e5765d9ac82/src/Node/UnreachableStatementNode.php
 *
 * @see \Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\RemoveUnreachableStatementRectorTest
 */
final class RemoveUnreachableStatementRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove unreachable statements', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\For_::class, \PhpParser\Node\Stmt\Foreach_::class, \PhpParser\Node\Stmt\Do_::class, \PhpParser\Node\Stmt\While_::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Stmt\If_::class, \PhpParser\Node\Stmt\ElseIf_::class, \PhpParser\Node\Stmt\Else_::class, \PhpParser\Node\Stmt\Case_::class, \PhpParser\Node\Stmt\TryCatch::class, \PhpParser\Node\Stmt\Catch_::class, \PhpParser\Node\Stmt\Finally_::class];
    }
    /**
     * @param For_|Foreach_|Do_|While_|ClassMethod|Function_|Closure|If_|ElseIf_|Else_|Case_|TryCatch|Catch_|Finally_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
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
            if ($stmt instanceof \PhpParser\Node\Stmt\Nop) {
                continue;
            }
            $previousStmt = $stmts[$key - 1];
            if ($this->shouldRemove($previousStmt, $stmt)) {
                \array_splice($stmts, $key);
                return $stmts;
            }
        }
        return $stmts;
    }
    private function shouldRemove(\PhpParser\Node\Stmt $previousStmt, \PhpParser\Node\Stmt $currentStmt) : bool
    {
        if ($currentStmt instanceof \PhpParser\Node\Stmt\InlineHTML) {
            return \false;
        }
        if ($previousStmt instanceof \PhpParser\Node\Stmt\Throw_) {
            return \true;
        }
        if ($previousStmt instanceof \PhpParser\Node\Stmt\Expression && $previousStmt->expr instanceof \PhpParser\Node\Expr\Exit_) {
            return \true;
        }
        if ($previousStmt instanceof \PhpParser\Node\Stmt\Goto_ && $currentStmt instanceof \PhpParser\Node\Stmt\Label) {
            return \false;
        }
        if (\in_array(\get_class($previousStmt), [\PhpParser\Node\Stmt\Return_::class, \PhpParser\Node\Stmt\Break_::class, \PhpParser\Node\Stmt\Continue_::class, \PhpParser\Node\Stmt\Goto_::class], \true)) {
            return \true;
        }
        if (!$previousStmt instanceof \PhpParser\Node\Stmt\TryCatch) {
            return \false;
        }
        $isUnreachable = $currentStmt->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::IS_UNREACHABLE);
        return $isUnreachable === \true && $previousStmt->finally instanceof \PhpParser\Node\Stmt\Finally_ && $this->cleanNop($previousStmt->finally->stmts) !== [];
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function cleanNop(array $stmts) : array
    {
        return \array_filter($stmts, function (\PhpParser\Node\Stmt $stmt) : bool {
            return !$stmt instanceof \PhpParser\Node\Stmt\Nop;
        });
    }
}
