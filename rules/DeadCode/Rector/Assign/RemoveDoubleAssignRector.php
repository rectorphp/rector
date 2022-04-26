<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveDoubleAssignRector\RemoveDoubleAssignRectorTest
 */
final class RemoveDoubleAssignRector extends AbstractRector
{
    public function __construct(
        private readonly SideEffectNodeDetector $sideEffectNodeDetector,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Simplify useless double assigns', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$value = 1;
$value = 1;
CODE_SAMPLE
                ,
                '$value = 1;'
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [
            Foreach_::class,
            FileWithoutNamespace::class,
            ClassMethod::class,
            Function_::class,
            Closure::class,
            If_::class,
            Namespace_::class,
        ];
    }

    /**
     * @param Foreach_|FileWithoutNamespace|If_|Namespace_|ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        $stmts = $node->stmts;
        if ($stmts === null) {
            return null;
        }

        $hasChanged = false;

        $previousStmt = null;

        foreach ($stmts as $key => $stmt) {
            if (! $stmt instanceof Expression) {
                $previousStmt = $stmt;
                continue;
            }

            $expr = $stmt->expr;
            if (! $expr instanceof Assign) {
                $previousStmt = $stmt;
                continue;
            }

            if (! $expr->var instanceof Variable && ! $expr->var instanceof PropertyFetch && ! $expr->var instanceof StaticPropertyFetch) {
                $previousStmt = $stmt;
                continue;
            }

            if (! $previousStmt instanceof Expression) {
                $previousStmt = $stmt;
                continue;
            }

            if (! $previousStmt->expr instanceof Assign) {
                $previousStmt = $stmt;
                continue;
            }

            $previousAssign = $previousStmt->expr;
            if (! $this->nodeComparator->areNodesEqual($previousAssign->var, $expr->var)) {
                $previousStmt = $stmt;
                continue;
            }

            // early check self referencing, ensure that variable not re-used
            if ($this->isSelfReferencing($expr)) {
                $previousStmt = $stmt;
                continue;
            }

            // detect call expression has side effect
            // no calls on right, could hide e.g. array_pop()|array_shift()
            if ($this->sideEffectNodeDetector->detectCallExpr($previousAssign->expr)) {
                $previousStmt = $stmt;
                continue;
            }

            // remove previous stmt, not current one as the current one is the override
            unset($stmts[$key - 1]);

            $hasChanged = true;
        }

        if (! $hasChanged) {
            return null;
        }

        ksort($stmts);

        $node->stmts = $stmts;
        return $node;
    }

    private function isSelfReferencing(Assign $assign): bool
    {
        return (bool) $this->betterNodeFinder->findFirst(
            $assign->expr,
            fn (Node $subNode): bool => $this->nodeComparator->areNodesEqual($assign->var, $subNode)
        );
    }
}
