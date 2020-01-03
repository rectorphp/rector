<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveImmediateAssignUseRector\RemoveImmediateAssignUseRectorTest
 */
final class RemoveImmediateAssignUseRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Remove assign that is immediately used in some condition later and use it directy',
            [
                new CodeSample(
                    <<<'PHP'
class SomeClass
{
    public function run($remix)
    {
        $value = $remix->getThis();
        if ($value > 3) {
            return true;
        }
    }
}
PHP
,
                    <<<'PHP'
class SomeClass
{
    public function run($remix)
    {
        if ($remix->getThis() > 3) {
            return true;
        }
    }
}
PHP

                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FunctionLike::class];
    }

    /**
     * @param FunctionLike $node
     */
    public function refactor(Node $node): ?Node
    {
        $onlyAssignedVariablesToExpression = $this->collectOnlyAssignedVariablesToExpresion($node);

        $this->traverseNodesWithCallable((array) $node->getStmts(), function (Node $node) use (
            $onlyAssignedVariablesToExpression
        ) {
            if ($node instanceof Assign) {
                $this->refactorAssign($node, $onlyAssignedVariablesToExpression);
            }

            if ($node instanceof Variable) {
                return $this->refactorVariable($node, $onlyAssignedVariablesToExpression);
            }

            return null;
        });

        return $node;
    }

    private function collectOnlyAssignedVariablesToExpresion(FunctionLike $functionLike): array
    {
        $assignedVariables = $this->collectAssignedVariables($functionLike);
        $usedVariables = $this->collectUsedVariables($functionLike);

        $onlyAssignedVariablesToExpression = [];

        foreach ($assignedVariables as $variableName => $variableAssigns) {
            // we accept → only one assign
            if (count($variableAssigns) > 1) {
                continue;
            }

            // the variable have to used
            if (! isset($usedVariables[$variableName])) {
                continue;
            }

            // no more than once
            if (count($usedVariables[$variableName]) > 1) {
                continue;
            }

            $onlyAssignedVariablesToExpression[$variableName] = $variableAssigns[0];
        }

        return $onlyAssignedVariablesToExpression;
    }

    /**
     * @return Variable[][][]|Expr[][][]
     */
    private function collectAssignedVariables(FunctionLike $functionLike): array
    {
        $assignedVariables = [];

        $this->traverseNodesWithCallable((array) $functionLike->getStmts(), function (Node $node) use (
            &$assignedVariables
        ) {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $node->var instanceof Variable) {
                return null;
            }

            $variableName = $this->getName($node->var);
            if ($variableName === null) {
                return null;
            }

            $assignedVariables[$variableName][] = [
                'variable' => $node->var,
                'expression' => $node->expr,
            ];
        });

        return $assignedVariables;
    }

    /**
     * @return Variable[][]
     */
    private function collectUsedVariables(FunctionLike $functionLike): array
    {
        $usedVariables = [];
        $this->traverseNodesWithCallable((array) $functionLike->getStmts(), function (Node $node) use (
            &$usedVariables
        ) {
            if (! $node instanceof Variable) {
                return null;
            }

            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Assign) {
                return null;
            }

            $variableName = $this->getName($node);
            if ($variableName === null) {
                return null;
            }

            $usedVariables[$variableName][] = $node;
        });
        return $usedVariables;
    }

    private function refactorVariable(Variable $variable, array $onlyAssignedVariablesToExpression): ?Expr
    {
        foreach ($onlyAssignedVariablesToExpression as $onlyAssignedVariableToExpression) {
            if (! $this->areNodesEqual($variable, $onlyAssignedVariableToExpression['variable'])) {
                continue;
            }

            return $onlyAssignedVariableToExpression['expression'];
        }

        return null;
    }

    private function refactorAssign(Assign $assign, array $onlyAssignedVariablesToExpression): void
    {
        foreach ($onlyAssignedVariablesToExpression as $onlyAssignedVariableToExpression) {
            if ($assign->var !== $onlyAssignedVariableToExpression['variable']) {
                continue;
            }

            $this->removeNode($assign);
        }
    }
}
