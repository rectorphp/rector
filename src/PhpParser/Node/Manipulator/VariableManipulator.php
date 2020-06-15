<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class VariableManipulator
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var AssignManipulator
     */
    private $assignManipulator;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var ArrayManipulator
     */
    private $arrayManipulator;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        AssignManipulator $assignManipulator,
        BetterStandardPrinter $betterStandardPrinter,
        BetterNodeFinder $betterNodeFinder,
        ArrayManipulator $arrayManipulator,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->assignManipulator = $assignManipulator;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->arrayManipulator = $arrayManipulator;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return Assign[]
     */
    public function collectScalarOrArrayAssignsOfVariable(ClassMethod $classMethod): array
    {
        $assignsOfArrayToVariable = [];

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->getStmts(), function (Node $node) use (
            &$assignsOfArrayToVariable
        ) {
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
        });

        return $assignsOfArrayToVariable;
    }

    /**
     * @param Assign[] $assignsOfArrayToVariable
     * @return Assign[]
     */
    public function filterOutReadOnlyVariables(array $assignsOfArrayToVariable, ClassMethod $classMethod): array
    {
        return array_filter($assignsOfArrayToVariable, function (Assign $assign) use ($classMethod) {
            /** @var Variable $variable */
            $variable = $assign->var;

            return $this->isReadOnlyVariable($classMethod, $variable, $assign);
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
     * @see \Rector\Core\PhpParser\Node\Manipulator\PropertyManipulator::isReadOnlyProperty()
     */
    private function isReadOnlyVariable(ClassMethod $classMethod, Variable $variable, Assign $assign): bool
    {
        $variableUsages = $this->betterNodeFinder->find((array) $classMethod->getStmts(), function (Node $node) use (
            $variable,
            $assign
        ) {
            if (! $node instanceof Variable) {
                return false;
            }

            // skip initialization
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode === $assign) {
                return false;
            }

            return $this->betterStandardPrinter->areNodesEqual($node, $variable);
        });

        foreach ($variableUsages as $variableUsage) {
            if (! $this->assignManipulator->isNodeLeftPartOfAssign($variableUsage)) {
                continue;
            }

            return false;
        }

        return true;
    }
}
