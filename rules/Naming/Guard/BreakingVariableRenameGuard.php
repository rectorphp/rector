<?php

declare (strict_types=1);
namespace Rector\Naming\Guard;

use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use Rector\Naming\Naming\ConflictingNameResolver;
use Rector\Naming\Naming\OverriddenExistingNamesResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
/**
 * This class check if a variable name change breaks existing code in class method
 */
final class BreakingVariableRenameGuard
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ConflictingNameResolver $conflictingNameResolver;
    /**
     * @readonly
     */
    private OverriddenExistingNamesResolver $overriddenExistingNamesResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, ConflictingNameResolver $conflictingNameResolver, OverriddenExistingNamesResolver $overriddenExistingNamesResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->conflictingNameResolver = $conflictingNameResolver;
        $this->overriddenExistingNamesResolver = $overriddenExistingNamesResolver;
    }
    public function shouldSkipVariable(string $currentName, string $expectedName, ClassMethod $classMethod, Variable $variable): bool
    {
        // is the suffix? → also accepted
        $expectedNameCamelCase = ucfirst($expectedName);
        if (substr_compare($currentName, $expectedNameCamelCase, -strlen($expectedNameCamelCase)) === 0) {
            return \true;
        }
        if ($this->conflictingNameResolver->hasNameIsInFunctionLike($expectedName, $classMethod)) {
            return \true;
        }
        if ($this->overriddenExistingNamesResolver->hasNameInClassMethodForNew($currentName, $classMethod)) {
            return \true;
        }
        if ($this->isVariableAlreadyDefined($variable, $currentName)) {
            return \true;
        }
        return $this->hasConflictVariable($classMethod, $expectedName);
    }
    private function isVariableAlreadyDefined(Variable $variable, string $currentVariableName): bool
    {
        $scope = $variable->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        $trinaryLogic = $scope->hasVariableType($currentVariableName);
        if ($trinaryLogic->yes()) {
            return \true;
        }
        return $trinaryLogic->maybe();
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    private function hasConflictVariable($functionLike, string $newName): bool
    {
        if ($functionLike instanceof ArrowFunction) {
            return $this->betterNodeFinder->hasInstanceOfName(array_merge([$functionLike->expr], $functionLike->params), Variable::class, $newName);
        }
        return $this->betterNodeFinder->hasInstanceOfName(array_merge((array) $functionLike->stmts, $functionLike->params), Variable::class, $newName);
    }
}
