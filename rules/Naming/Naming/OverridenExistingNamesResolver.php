<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\PhpArray\ArrayFilter;
use Rector\NodeNameResolver\NodeNameResolver;
final class OverridenExistingNamesResolver
{
    /**
     * @var array<string, array<int, string>>
     */
    private $overridenExistingVariableNamesByClassMethod = [];
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var ArrayFilter
     */
    private $arrayFilter;
    public function __construct(\Rector\Naming\PhpArray\ArrayFilter $arrayFilter, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->arrayFilter = $arrayFilter;
    }
    /**
     * @param ClassMethod|Function_|Closure $functionLike
     */
    public function checkNameInClassMethodForNew(string $variableName, \PhpParser\Node\FunctionLike $functionLike) : bool
    {
        $overridenVariableNames = $this->resolveOveriddenNamesForNew($functionLike);
        return \in_array($variableName, $overridenVariableNames, \true);
    }
    public function checkNameInClassMethodForParam(string $expectedName, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, \PhpParser\Node\Expr\Assign::class);
        $usedVariableNames = [];
        foreach ($assigns as $assign) {
            if (!$assign->var instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            $variableName = $this->nodeNameResolver->getName($assign->var);
            if ($variableName === null) {
                continue;
            }
            $usedVariableNames[] = $variableName;
        }
        return \in_array($expectedName, $usedVariableNames, \true);
    }
    /**
     * @param ClassMethod|Function_|Closure $functionLike
     * @return string[]
     */
    private function resolveOveriddenNamesForNew(\PhpParser\Node\FunctionLike $functionLike) : array
    {
        $classMethodHash = \spl_object_hash($functionLike);
        if (isset($this->overridenExistingVariableNamesByClassMethod[$classMethodHash])) {
            return $this->overridenExistingVariableNamesByClassMethod[$classMethodHash];
        }
        $currentlyUsedNames = [];
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $functionLike->stmts, \PhpParser\Node\Expr\Assign::class);
        foreach ($assigns as $assign) {
            /** @var Variable $assignVariable */
            $assignVariable = $assign->var;
            $currentVariableName = $this->nodeNameResolver->getName($assignVariable);
            if ($currentVariableName === null) {
                continue;
            }
            $currentlyUsedNames[] = $currentVariableName;
        }
        $currentlyUsedNames = \array_values($currentlyUsedNames);
        $currentlyUsedNames = $this->arrayFilter->filterWithAtLeastTwoOccurences($currentlyUsedNames);
        $this->overridenExistingVariableNamesByClassMethod[$classMethodHash] = $currentlyUsedNames;
        return $currentlyUsedNames;
    }
}
