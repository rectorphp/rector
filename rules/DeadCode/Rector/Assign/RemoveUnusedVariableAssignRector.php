<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\RemoveUnusedVariableAssignRectorTest
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
     * @return array<class-string<Node>>
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
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            $ifNode = $parentNode->getAttribute(AttributeKey::NEXT_NODE);

            // check if next node is if
            if (! $ifNode instanceof If_) {
                return null;
            }

            return $this->searchIfAndElseForVariableRedeclaration($node, $ifNode);
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Expression) {
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

    private function searchIfAndElseForVariableRedeclaration(Assign $node, If_ $ifNode): ?Node
    {
        /** @var Variable $varNode */
        $varNode = $node->var;

        // search if for redeclaration of variable
        /** @var Node\Stmt\Expression $statementIf */
        foreach ($ifNode->stmts as $statementIf) {
            if (! $statementIf->expr instanceof Assign) {
                continue;
            }

            /** @var Variable $varIf */
            $varIf = $statementIf->expr->var;
            if ($varNode->name !== $varIf->name) {
                continue;
            }

            $elseNode = $ifNode->else;
            if (! $elseNode instanceof Else_) {
                continue;
            }

            // search else for redeclaration of variable
            /** @var Node\Stmt\Expression $statementElse */
            foreach ($elseNode->stmts as $statementElse) {
                if (! $statementElse->expr instanceof Assign) {
                    continue;
                }

                /** @var Variable $varElse */
                $varElse = $statementElse->expr->var;
                if ($varNode->name !== $varElse->name) {
                    continue;
                }

                $this->removeNode($node);
                return $node;
            }
        }

        return null;
    }
}
