<?php

declare(strict_types=1);

namespace Rector\Naming\Guard;

use DateTimeInterface;
use Nette\Utils\Strings;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\Naming\ConflictingNameResolver;
use Rector\Naming\Naming\OverridenExistingNamesResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;

/**
 * This class check if a variable name change breaks existing code in class method
 */
final class BreakingVariableRenameGuard
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var ConflictingNameResolver
     */
    private $conflictingNameResolver;

    /**
     * @var OverridenExistingNamesResolver
     */
    private $overridenExistingNamesResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var TypeUnwrapper
     */
    private $typeUnwrapper;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        ConflictingNameResolver $conflictingNameResolver,
        NodeTypeResolver $nodeTypeResolver,
        OverridenExistingNamesResolver $overridenExistingNamesResolver,
        TypeUnwrapper $typeUnwrapper
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->conflictingNameResolver = $conflictingNameResolver;
        $this->overridenExistingNamesResolver = $overridenExistingNamesResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeUnwrapper = $typeUnwrapper;
    }

    /**
     * @param ClassMethod|Function_|Closure $functionLike
     */
    public function shouldSkipVariable(
        string $currentName,
        string $expectedName,
        FunctionLike $functionLike,
        Variable $variable
    ): bool {
        // is the suffix? → also accepted
        if (Strings::endsWith($currentName, ucfirst($expectedName))) {
            return true;
        }

        if ($this->conflictingNameResolver->checkNameIsInFunctionLike($expectedName, $functionLike)) {
            return true;
        }

        if ($this->overridenExistingNamesResolver->checkNameInClassMethodForNew($currentName, $functionLike)) {
            return true;
        }

        if ($this->isVariableAlreadyDefined($variable, $currentName)) {
            return true;
        }

        if ($this->skipOnConflictOtherVariable($functionLike, $expectedName)) {
            return true;
        }

        if ($this->isUsedInClosureUsesName($expectedName, $functionLike)) {
            return true;
        }

        return $this->isUsedInIfAndOtherBranches($variable, $currentName);
    }

    public function shouldSkipProperty(Property $property, string $currentName): bool
    {
        $propertyType = $this->nodeTypeResolver->resolve($property);
        return $this->isDateTimeAtNamingConvention($propertyType, $currentName);
    }

    public function shouldSkipParam(
        string $currentName,
        string $expectedName,
        ClassMethod $classMethod,
        Param $param
    ): bool {
        // is the suffix? → also accepted
        if (Strings::endsWith($currentName, ucfirst($expectedName))) {
            return true;
        }

        $conflictingNames = $this->conflictingNameResolver->resolveConflictingVariableNamesForParam($classMethod);
        if (in_array($expectedName, $conflictingNames, true)) {
            return true;
        }

        if ($this->conflictingNameResolver->checkNameIsInFunctionLike($expectedName, $classMethod)) {
            return true;
        }

        if ($this->overridenExistingNamesResolver->checkNameInClassMethodForParam($expectedName, $classMethod)) {
            return true;
        }

        if ($this->isVariableAlreadyDefined($param->var, $currentName)) {
            return true;
        }

        $paramType = $this->nodeTypeResolver->getStaticType($param);

        return $this->isDateTimeAtNamingConvention($paramType, $currentName);
    }

    private function isVariableAlreadyDefined(Variable $variable, string $currentVariableName): bool
    {
        $scope = $variable->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return false;
        }

        $trinaryLogic = $scope->hasVariableType($currentVariableName);
        if ($trinaryLogic->yes()) {
            return true;
        }

        return $trinaryLogic->maybe();
    }

    private function isUsedInIfAndOtherBranches(Variable $variable, string $currentVariableName): bool
    {
        // is in if branches?
        $previousIf = $this->betterNodeFinder->findFirstPreviousOfTypes($variable, [If_::class]);
        if ($previousIf instanceof If_) {
            $variableUses = [];

            $variableUses[] = $this->betterNodeFinder->findVariableOfName($previousIf->stmts, $currentVariableName);

            $previousStmts = $previousIf->else !== null ? $previousIf->else->stmts : [];
            $variableUses[] = $this->betterNodeFinder->findVariableOfName($previousStmts, $currentVariableName);
            $variableUses[] = $this->betterNodeFinder->findVariableOfName($previousIf->elseifs, $currentVariableName);

            $variableUses = array_filter($variableUses);
            if (count($variableUses) > 1) {
                return true;
            }
        }

        return false;
    }

    private function isDateTimeAtNamingConvention(Type $type, string $currentName): bool
    {
        $type = $this->typeUnwrapper->unwrapFirstObjectTypeFromUnionType($type);
        if (! $type instanceof TypeWithClassName) {
            return false;
        }

        if (! is_a($type->getClassName(), DateTimeInterface::class, true)) {
            return false;
        }

        return (bool) Strings::match($currentName, '#[\w+]At$#');
    }

    /**
     * @param ClassMethod|Function_|Closure $functionLike
     */
    private function isUsedInClosureUsesName(string $expectedName, FunctionLike $functionLike): bool
    {
        if (! $functionLike instanceof Closure) {
            return false;
        }

        return $this->betterNodeFinder->hasVariableOfName((array) $functionLike->uses, $expectedName);
    }

    /**
     * @param ClassMethod|Function_|Closure $functionLike
     */
    private function skipOnConflictOtherVariable(FunctionLike $functionLike, string $newName): bool
    {
        return $this->betterNodeFinder->hasInstanceOfName((array) $functionLike->stmts, Variable::class, $newName);
    }
}
