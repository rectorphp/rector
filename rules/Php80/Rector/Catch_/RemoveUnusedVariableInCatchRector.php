<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Catch_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Catch_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/non-capturing_catches
 *
 * @see \Rector\Tests\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector\RemoveUnusedVariableInCatchRectorTest
 */
final class RemoveUnusedVariableInCatchRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused variable in catch()', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        try {
        } catch (Throwable $notUsedThrowable) {
        }
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        try {
        } catch (Throwable) {
        }
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
        return [Catch_::class];
    }

    /**
     * @param Catch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $caughtVar = $node->var;
        if (! $caughtVar instanceof Variable) {
            return null;
        }

        if ($this->isVariableUsedInStmts($node->stmts, $caughtVar)) {
            return null;
        }

        if ($this->isVariableUsedNext($node, $caughtVar)) {
            return null;
        }

        $node->var = null;

        return $node;
    }

    /**
     * @param Node[] $nodes
     */
    private function isVariableUsedInStmts(array $nodes, Variable $variable): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($nodes, function (Node $node) use ($variable): bool {
            if (! $node instanceof Variable) {
                return false;
            }

            return $this->nodeComparator->areNodesEqual($node, $variable);
        });
    }

    private function isVariableUsedNext(Catch_ $catch, Variable $variable): bool
    {
        return (bool) $this->betterNodeFinder->findFirstNext(
            $catch,
            fn (Node $node): bool => $this->nodeComparator->areNodesEqual($node, $variable)
        );
    }
}
