<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Nop;
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
        $stmts = $node->stmts;

        $isPassedTheUnreachable = false;
        $hasChanged = false;

        foreach ($stmts as $key => $stmt) {
            if ($this->shouldSkipNode($stmt)) {
                continue;
            }

            if ($this->isUnreachable($stmt)) {
                $isPassedTheUnreachable = true;
            }

            if ($this->isAfterMarkTestSkippedMethodCall($stmt)) {
                // keep for test case temporary ignored
                break;
            }

            if (! $isPassedTheUnreachable) {
                continue;
            }

            unset($stmts[$key]);
            $hasChanged = true;
        }

        if (! $hasChanged) {
            return null;
        }

        $node->stmts = $stmts;
        return $node;
    }

    private function shouldSkipNode(Stmt $stmt): bool
    {
        return $stmt instanceof Nop;
    }

    private function isUnreachable(Stmt $stmt): bool
    {
        $isUnreachable = $stmt->getAttribute(AttributeKey::IS_UNREACHABLE);

        return $isUnreachable === true;
    }

    /**
     * Keep content after markTestSkipped(), intentional temporary
     */
    private function isAfterMarkTestSkippedMethodCall(Stmt $stmt): bool
    {
        if (! $stmt instanceof Expression) {
            return false;
        }

        $expr = $stmt->expr;
        if ($expr instanceof MethodCall || $expr instanceof StaticCall) {
            return $this->isNames($expr->name, ['markTestSkipped', 'markTestIncomplete']);
        }

        return false;
    }
}
