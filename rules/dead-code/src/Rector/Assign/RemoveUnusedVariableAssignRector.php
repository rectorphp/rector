<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedVariableAssignRector\RemoveUnusedVariableAssignRectorTest
 */
final class RemoveUnusedVariableAssignRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused assigns to variables', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $value = 5;
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        $classMethod = $node->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethod instanceof FunctionLike) {
            return null;
        }

        if (! $node->var instanceof Variable) {
            return null;
        }

        // variable is used
        $variableUsages = $this->findVariableUsages($classMethod, $node);
        if (count($variableUsages) > 0) {
            return null;
        }

        $this->removeNode($node);

        return $node;
    }

    /**
     * @return Variable[]
     */
    private function findVariableUsages(FunctionLike $functionLike, Assign $assign): array
    {
        return $this->betterNodeFinder->find((array) $functionLike->getStmts(), function (Node $node) use (
            $assign
        ): bool {
            if (! $node instanceof Variable) {
                return false;
            }

            // skip assign value
            return $assign->var !== $node;
        });
    }
}
