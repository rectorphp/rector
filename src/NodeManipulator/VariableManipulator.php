<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\NodeAnalyzer\ExprAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ReadWrite\Guard\VariableToConstantGuard;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class VariableManipulator
{
    public function __construct(
        private readonly AssignManipulator $assignManipulator,
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly VariableToConstantGuard $variableToConstantGuard,
        private readonly NodeComparator $nodeComparator,
        private readonly ExprAnalyzer $exprAnalyzer
    ) {
    }

    /**
     * @return Assign[]
     */
    public function collectScalarOrArrayAssignsOfVariable(ClassMethod $classMethod): array
    {
        $currentClass = $this->betterNodeFinder->findParentType($classMethod, Class_::class);
        if (! $currentClass instanceof Class_) {
            return [];
        }

        $currentClassName = (string) $this->nodeNameResolver->getName($currentClass);
        $assignsOfArrayToVariable = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->getStmts(),
            function (Node $node) use (&$assignsOfArrayToVariable, $currentClass, $currentClassName) {
                if (! $node instanceof Assign) {
                    return null;
                }

                if (! $node->var instanceof Variable) {
                    return null;
                }

                if ($this->exprAnalyzer->isDynamicValue($node->expr)) {
                    return null;
                }

                if ($this->hasEncapsedStringPart($node->expr)) {
                    return null;
                }

                if ($this->isTestCaseExpectedVariable($node->var)) {
                    return null;
                }

                if ($node->expr instanceof ConstFetch) {
                    return null;
                }

                if ($node->expr instanceof ClassConstFetch && $this->isOutsideClass(
                    $node->expr,
                    $currentClass,
                    $currentClassName
                )) {
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
        return array_filter(
            $assignsOfArrayToVariable,
            fn (Assign $assign): bool => $this->isReadOnlyVariable($classMethod, $assign)
        );
    }

    private function isOutsideClass(
        ClassConstFetch $classConstFetch,
        Class_ $currentClass,
        string $currentClassName
    ): bool {
        /**
         * Dynamic class already checked on $this->exprAnalyzer->isDynamicValue() early
         * @var Name $class
         */
        $class = $classConstFetch->class;
        if ($this->nodeNameResolver->isName($class, 'self')) {
            return $currentClass->extends instanceof FullyQualified;
        }

        return ! $this->nodeNameResolver->isName($class, $currentClassName);
    }

    private function hasEncapsedStringPart(Expr $expr): bool
    {
        return (bool) $this->betterNodeFinder->findFirst(
            $expr,
            fn (Node $subNode): bool => $subNode instanceof Encapsed || $subNode instanceof EncapsedStringPart
        );
    }

    private function isTestCaseExpectedVariable(Variable $variable): bool
    {
        $classLike = $this->betterNodeFinder->findParentType($variable, ClassLike::class);
        if (! $classLike instanceof ClassLike) {
            return false;
        }

        $className = (string) $this->nodeNameResolver->getName($classLike);
        if (! \str_ends_with($className, 'Test')) {
            return false;
        }

        return $this->nodeNameResolver->isName($variable, 'expect*');
    }

    /**
     * Inspiration
     * @see \Rector\Core\NodeManipulator\PropertyManipulator::isPropertyUsedInReadContext()
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
