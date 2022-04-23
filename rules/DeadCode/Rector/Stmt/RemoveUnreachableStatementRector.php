<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\TryCatch;
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
        return [Foreach_::class, FunctionLike::class, Else_::class, If_::class];
    }

    /**
     * @param Foreach_|FunctionLike|Else_|If_ $node
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
        if ($previousStmt instanceof Throw_) {
            return true;
        }

        if ($previousStmt instanceof Expression && $previousStmt->expr instanceof Exit_) {
            return true;
        }

        if ($previousStmt instanceof Return_) {
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
