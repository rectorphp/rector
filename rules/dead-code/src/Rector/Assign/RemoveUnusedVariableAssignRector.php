<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedVariableAssignRector\RemoveUnusedVariableAssignRectorTest
 */
final class RemoveUnusedVariableAssignRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused assigns to variables', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
    }
}
CODE_SAMPLE
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
        if ($variableUsages !== []) {
            return null;
        }

        /** @var FuncCall|MethodCall|StaticCall|NullsafeMethodCall|New_|null $expr */
        $expr = $this->betterNodeFinder->findParentTypes($node, [
            FuncCall::class,
            MethodCall::class,
            StaticCall::class,
            NullsafeMethodCall::class,
            New_::class,
        ]);

        if ($expr === null) {
            $this->removeNode($node);
            return $node;
        }

        if (count($expr->args) !== 1) {
            return null;
        }

        if ($this->areNodesEqual($expr->args[0]->value, $node)) {
            $this->removeNode($expr);
            return $node;
        }

        return null;
    }

    /**
     * @return Variable[]
     */
    private function findVariableUsages(ClassMethod $classMethod, Assign $assign): array
    {
        return $this->betterNodeFinder->find((array) $classMethod->getStmts(), function (Node $node) use (
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
