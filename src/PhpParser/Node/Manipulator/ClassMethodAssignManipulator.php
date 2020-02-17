<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;

final class ClassMethodAssignManipulator
{
    /**
     * @var VariableManipulator
     */
    private $variableManipulator;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        VariableManipulator $variableManipulator,
        CallableNodeTraverser $callableNodeTraverser,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->variableManipulator = $variableManipulator;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return Assign[]
     */
    public function collectReadyOnlyAssignScalarVariables(ClassMethod $classMethod): array
    {
        $assignsOfScalarOrArrayToVariable = $this->variableManipulator->collectScalarOrArrayAssignsOfVariable(
            $classMethod
        );

        // filter out [$value] = $array, array destructing
        $readOnlyVariableAssigns = $this->filterOutArrayDestructedVariables(
            $assignsOfScalarOrArrayToVariable,
            $classMethod
        );

        return $this->variableManipulator->filterOutReadOnlyVariables($readOnlyVariableAssigns, $classMethod);
    }

    /**
     * @param Assign[] $variableAssigns
     * @return Assign[]
     */
    private function filterOutArrayDestructedVariables(array $variableAssigns, ClassMethod $classMethod): array
    {
        $arrayDestructionCreatedVariables = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) use (
            &$arrayDestructionCreatedVariables
        ) {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $node->var instanceof Array_ && ! $node->var instanceof List_) {
                return null;
            }

            foreach ($node->var->items as $arrayItem) {
                if (! $arrayItem->value instanceof Variable) {
                    continue;
                }

                /** @var string $variableName */
                $variableName = $this->nodeNameResolver->getName($arrayItem->value);
                $arrayDestructionCreatedVariables[] = $variableName;
            }
        });

        return array_filter($variableAssigns, function (Assign $assign) use ($arrayDestructionCreatedVariables) {
            return ! $this->nodeNameResolver->isNames($assign->var, $arrayDestructionCreatedVariables);
        });
    }
}
