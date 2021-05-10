<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ReadWrite\Guard\VariableToConstantGuard;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class VariableManipulator
{
    public function __construct(
        private ArrayManipulator $arrayManipulator,
        private AssignManipulator $assignManipulator,
        private BetterNodeFinder $betterNodeFinder,
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private NodeNameResolver $nodeNameResolver,
        private VariableToConstantGuard $variableToConstantGuard,
        private NodeComparator $nodeComparator
    ) {
    }

    /**
     * @return Assign[]
     */
    public function collectScalarOrArrayAssignsOfVariable(ClassMethod $classMethod): array
    {
        $assignsOfArrayToVariable = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->getStmts(),
            function (Node $node) use (&$assignsOfArrayToVariable) {
                if (! $node instanceof Assign) {
                    return null;
                }

                if (! $node->var instanceof Variable) {
                    return null;
                }

                if (! $node->expr instanceof Array_ && ! $node->expr instanceof Scalar) {
                    return null;
                }

                if ($node->expr instanceof Encapsed) {
                    return null;
                }

                if ($node->expr instanceof Array_ && ! $this->arrayManipulator->isArrayOnlyScalarValues($node->expr)) {
                    return null;
                }

                if ($this->isTestCaseExpectedVariable($node->var)) {
                    return null;
                }

                $assignsOfArrayToVariable[] = $node;
            }
        );

        return $assignsOfArrayToVariable;
    }

    /**
     * @param Assign[] $assignsOfArrayToVariable
     * @return Assign[]
     */
    public function filterOutChangedVariables(array $assignsOfArrayToVariable, ClassMethod $classMethod): array
    {
        return array_filter($assignsOfArrayToVariable, function (Assign $assign) use ($classMethod): bool {
            return $this->isReadOnlyVariable($classMethod, $assign);
        });
    }

    private function isTestCaseExpectedVariable(Variable $variable): bool
    {
        /** @var string $className */
        $className = $variable->getAttribute(AttributeKey::CLASS_NAME);
        if (! Strings::endsWith($className, 'Test')) {
            return false;
        }

        return $this->nodeNameResolver->isName($variable, 'expect*');
    }

    /**
     * Inspiration
     * @see \Rector\Core\NodeManipulator\PropertyManipulator::isReadOnlyProperty()
     */
    private function isReadOnlyVariable(ClassMethod $classMethod, Assign $assign): bool
    {
        if (! $assign->var instanceof Variable) {
            return false;
        }

        $variable = $assign->var;
        $variableUsages = $this->collectVariableUsages($classMethod, $variable, $assign);

        foreach ($variableUsages as $variableUsage) {
            $parent = $variableUsage->getAttribute(AttributeKey::PARENT_NODE);
            if ($parent instanceof Arg && ! $this->variableToConstantGuard->isReadArg($parent)) {
                return false;
            }

            if (! $this->assignManipulator->isLeftPartOfAssign($variableUsage)) {
                continue;
            }

            return false;
        }

        return true;
    }

    /**
     * @return Variable[]
     */
    private function collectVariableUsages(ClassMethod $classMethod, Variable $variable, Assign $assign): array
    {
        return $this->betterNodeFinder->find((array) $classMethod->getStmts(), function (Node $node) use (
            $variable,
            $assign
        ): bool {
            if (! $node instanceof Variable) {
                return false;
            }

            // skip initialization
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode === $assign) {
                return false;
            }

            return $this->nodeComparator->areNodesEqual($node, $variable);
        });
    }
}
