<?php

declare(strict_types=1);

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
final class RemoveUnreachableStatementRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unreachable statements', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 5;

        $removeMe = 10;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 5;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [
            For_::class,
            Foreach_::class,
            Do_::class,
            While_::class,
            ClassMethod::class,
            Function_::class,
            Closure::class,
            If_::class,
            ElseIf_::class,
            Else_::class,
            Case_::class,
            TryCatch::class,
            Catch_::class,
            Finally_::class,
        ];
    }

    /**
     * @param For_|Foreach_|Do_|While_|ClassMethod|Function_|Closure|If_|ElseIf_|Else_|Case_|TryCatch|Catch_|Finally_ $node
     */
    public function refactor(Node $node): ?Node
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
    private function processCleanUpUnreachabelStmts(array $stmts): array
    {
        $originalStmts = $stmts;
        foreach ($stmts as $key => $stmt) {
            if (! isset($stmts[$key - 1])) {
                continue;
            }

            if ($stmt instanceof Nop) {
                continue;
            }

            $previousStmt = $stmts[$key - 1];

            if ($this->shouldRemove($previousStmt, $stmt)) {
                unset($stmts[$key]);
                break;
            }
        }

        if ($originalStmts === $stmts) {
            return $originalStmts;
        }

        $stmts = array_values($stmts);
        return $this->processCleanUpUnreachabelStmts($stmts);
    }

    private function shouldRemove(Stmt $previousStmt, Stmt $currentStmt): bool
    {
        if ($currentStmt instanceof InlineHTML) {
            return false;
        }

        if ($previousStmt instanceof Throw_) {
            return true;
        }

        if ($previousStmt instanceof Expression && $previousStmt->expr instanceof Exit_) {
            return true;
        }

        if ($previousStmt instanceof Goto_ && $currentStmt instanceof Label) {
            return false;
        }

        if (in_array($previousStmt::class, [Return_::class, Break_::class, Continue_::class, Goto_::class], true)) {
            return true;
        }

        if (! $previousStmt instanceof TryCatch) {
            return false;
        }

        $isUnreachable = $currentStmt->getAttribute(AttributeKey::IS_UNREACHABLE);
        return $isUnreachable === true && $previousStmt->finally instanceof Finally_ && $this->cleanNop(
            $previousStmt->finally->stmts
        ) !== [];
    }

    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function cleanNop(array $stmts): array
    {
        return array_filter($stmts, fn (Stmt $stmt): bool => ! $stmt instanceof Nop);
    }
}
