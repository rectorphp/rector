<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\NodeManipulator\FunctionLikeManipulator;
use Rector\PhpParser\Node\BetterNodeFinder;
final class ConflictingNameResolver
{
    /**
     * @readonly
     */
    private ArrayFilter $arrayFilter;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private \Rector\Naming\Naming\ExpectedNameResolver $expectedNameResolver;
    /**
     * @readonly
     */
    private MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver;
    /**
     * @readonly
     */
    private FunctionLikeManipulator $functionLikeManipulator;
    /**
     * @var array<int, string[]>
     */
    private array $conflictingVariableNamesByClassMethod = [];
    public function __construct(ArrayFilter $arrayFilter, BetterNodeFinder $betterNodeFinder, \Rector\Naming\Naming\ExpectedNameResolver $expectedNameResolver, MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver, FunctionLikeManipulator $functionLikeManipulator)
    {
        $this->arrayFilter = $arrayFilter;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->expectedNameResolver = $expectedNameResolver;
        $this->matchParamTypeExpectedNameResolver = $matchParamTypeExpectedNameResolver;
        $this->functionLikeManipulator = $functionLikeManipulator;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $classMethod
     */
    public function resolveConflictingVariableNamesForParam($classMethod) : array
    {
        $expectedNames = [];
        foreach ($classMethod->params as $param) {
            $expectedName = $this->matchParamTypeExpectedNameResolver->resolve($param);
            if ($expectedName === null) {
                continue;
            }
            $expectedNames[] = $expectedName;
        }
        return $this->arrayFilter->filterWithAtLeastTwoOccurrences($expectedNames);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    public function hasNameIsInFunctionLike(string $variableName, $functionLike) : bool
    {
        $conflictingVariableNames = $this->resolveConflictingVariableNamesForNew($functionLike);
        return \in_array($variableName, $conflictingVariableNames, \true);
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    private function resolveConflictingVariableNamesForNew($functionLike) : array
    {
        // cache it!
        $classMethodId = \spl_object_id($functionLike);
        if (isset($this->conflictingVariableNamesByClassMethod[$classMethodId])) {
            return $this->conflictingVariableNamesByClassMethod[$classMethodId];
        }
        $paramNames = $this->functionLikeManipulator->resolveParamNames($functionLike);
        $newAssignNames = $this->resolveForNewAssigns($functionLike);
        $nonNewAssignNames = $this->resolveForNonNewAssigns($functionLike);
        $protectedNames = \array_merge($paramNames, $newAssignNames, $nonNewAssignNames);
        $protectedNames = $this->arrayFilter->filterWithAtLeastTwoOccurrences($protectedNames);
        $this->conflictingVariableNamesByClassMethod[$classMethodId] = $protectedNames;
        return $protectedNames;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    private function resolveForNewAssigns($functionLike) : array
    {
        $names = [];
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $functionLike->getStmts(), Assign::class);
        foreach ($assigns as $assign) {
            $name = $this->expectedNameResolver->resolveForAssignNew($assign);
            if ($name === null) {
                continue;
            }
            $names[] = $name;
        }
        return $names;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    private function resolveForNonNewAssigns($functionLike) : array
    {
        $names = [];
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $functionLike->getStmts(), Assign::class);
        foreach ($assigns as $assign) {
            $name = $this->expectedNameResolver->resolveForAssignNonNew($assign);
            if ($name === null) {
                continue;
            }
            $names[] = $name;
        }
        return $names;
    }
}
