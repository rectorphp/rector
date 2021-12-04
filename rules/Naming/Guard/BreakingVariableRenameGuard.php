<?php

declare (strict_types=1);
namespace Rector\Naming\Guard;

use DateTimeInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Util\StringUtils;
use Rector\Naming\Naming\ConflictingNameResolver;
use Rector\Naming\Naming\OverridenExistingNamesResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
/**
 * This class check if a variable name change breaks existing code in class method
 */
final class BreakingVariableRenameGuard
{
    /**
     * @var string
     * @see https://regex101.com/r/1pKLgf/1
     */
    private const AT_NAMING_REGEX = '#[\\w+]At$#';
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\ConflictingNameResolver
     */
    private $conflictingNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\OverridenExistingNamesResolver
     */
    private $overridenExistingNamesResolver;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper
     */
    private $typeUnwrapper;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Naming\Naming\ConflictingNameResolver $conflictingNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Naming\Naming\OverridenExistingNamesResolver $overridenExistingNamesResolver, \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper $typeUnwrapper, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->conflictingNameResolver = $conflictingNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->overridenExistingNamesResolver = $overridenExistingNamesResolver;
        $this->typeUnwrapper = $typeUnwrapper;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function shouldSkipVariable(string $currentName, string $expectedName, $functionLike, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        // is the suffix? → also accepted
        $expectedNameCamelCase = \ucfirst($expectedName);
        if (\substr_compare($currentName, $expectedNameCamelCase, -\strlen($expectedNameCamelCase)) === 0) {
            return \true;
        }
        if ($this->conflictingNameResolver->hasNameIsInFunctionLike($expectedName, $functionLike)) {
            return \true;
        }
        if ($this->overridenExistingNamesResolver->hasNameInClassMethodForNew($currentName, $functionLike)) {
            return \true;
        }
        if ($this->isVariableAlreadyDefined($variable, $currentName)) {
            return \true;
        }
        if ($this->hasConflictVariable($functionLike, $expectedName)) {
            return \true;
        }
        if ($this->isUsedInClosureUsesName($expectedName, $functionLike)) {
            return \true;
        }
        if ($this->isUsedInForeachKeyValueVar($variable, $currentName)) {
            return \true;
        }
        return $this->isUsedInIfAndOtherBranches($variable, $currentName);
    }
    public function shouldSkipParam(string $currentName, string $expectedName, \PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Param $param) : bool
    {
        // is the suffix? → also accepted
        $expectedNameCamelCase = \ucfirst($expectedName);
        if (\substr_compare($currentName, $expectedNameCamelCase, -\strlen($expectedNameCamelCase)) === 0) {
            return \true;
        }
        $conflictingNames = $this->conflictingNameResolver->resolveConflictingVariableNamesForParam($classMethod);
        if (\in_array($expectedName, $conflictingNames, \true)) {
            return \true;
        }
        if ($this->conflictingNameResolver->hasNameIsInFunctionLike($expectedName, $classMethod)) {
            return \true;
        }
        if ($this->overridenExistingNamesResolver->hasNameInClassMethodForParam($expectedName, $classMethod)) {
            return \true;
        }
        if ($this->isVariableAlreadyDefined($param->var, $currentName)) {
            return \true;
        }
        if ($this->isRamseyUuidInterface($param)) {
            return \true;
        }
        if ($this->isDateTimeAtNamingConvention($param)) {
            return \true;
        }
        return (bool) $this->betterNodeFinder->find((array) $classMethod->stmts, function (\PhpParser\Node $node) use($expectedName) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $expectedName);
        });
    }
    private function isVariableAlreadyDefined(\PhpParser\Node\Expr\Variable $variable, string $currentVariableName) : bool
    {
        $scope = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $trinaryLogic = $scope->hasVariableType($currentVariableName);
        if ($trinaryLogic->yes()) {
            return \true;
        }
        return $trinaryLogic->maybe();
    }
    /**
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function hasConflictVariable($functionLike, string $newName) : bool
    {
        return $this->betterNodeFinder->hasInstanceOfName((array) $functionLike->stmts, \PhpParser\Node\Expr\Variable::class, $newName);
    }
    /**
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function isUsedInClosureUsesName(string $expectedName, $functionLike) : bool
    {
        if (!$functionLike instanceof \PhpParser\Node\Expr\Closure) {
            return \false;
        }
        return $this->betterNodeFinder->hasVariableOfName($functionLike->uses, $expectedName);
    }
    private function isUsedInForeachKeyValueVar(\PhpParser\Node\Expr\Variable $variable, string $currentName) : bool
    {
        $previousForeach = $this->betterNodeFinder->findFirstPreviousOfTypes($variable, [\PhpParser\Node\Stmt\Foreach_::class]);
        if ($previousForeach instanceof \PhpParser\Node\Stmt\Foreach_) {
            if ($previousForeach->keyVar === $variable) {
                return \false;
            }
            if ($previousForeach->valueVar === $variable) {
                return \false;
            }
            if ($this->nodeNameResolver->isName($previousForeach->valueVar, $currentName)) {
                return \true;
            }
            if ($previousForeach->keyVar === null) {
                return \false;
            }
            if ($this->nodeNameResolver->isName($previousForeach->keyVar, $currentName)) {
                return \true;
            }
        }
        return \false;
    }
    private function isUsedInIfAndOtherBranches(\PhpParser\Node\Expr\Variable $variable, string $currentVariableName) : bool
    {
        // is in if branches?
        $previousIf = $this->betterNodeFinder->findFirstPreviousOfTypes($variable, [\PhpParser\Node\Stmt\If_::class]);
        if ($previousIf instanceof \PhpParser\Node\Stmt\If_) {
            $variableUses = [];
            $variableUses[] = $this->betterNodeFinder->findVariableOfName($previousIf->stmts, $currentVariableName);
            $previousStmts = $previousIf->else !== null ? $previousIf->else->stmts : [];
            $variableUses[] = $this->betterNodeFinder->findVariableOfName($previousStmts, $currentVariableName);
            $variableUses[] = $this->betterNodeFinder->findVariableOfName($previousIf->elseifs, $currentVariableName);
            $variableUses = \array_filter($variableUses);
            if (\count($variableUses) > 1) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @TODO Remove once ParamRenamer created
     */
    private function isRamseyUuidInterface(\PhpParser\Node\Param $param) : bool
    {
        return $this->nodeTypeResolver->isObjectType($param, new \PHPStan\Type\ObjectType('Ramsey\\Uuid\\UuidInterface'));
    }
    /**
     * @TODO Remove once ParamRenamer created
     */
    private function isDateTimeAtNamingConvention(\PhpParser\Node\Param $param) : bool
    {
        $type = $this->nodeTypeResolver->getType($param);
        $type = $this->typeUnwrapper->unwrapFirstObjectTypeFromUnionType($type);
        if (!$type instanceof \PHPStan\Type\TypeWithClassName) {
            return \false;
        }
        if (!\is_a($type->getClassName(), \DateTimeInterface::class, \true)) {
            return \false;
        }
        /** @var string $currentName */
        $currentName = $this->nodeNameResolver->getName($param);
        return \Rector\Core\Util\StringUtils::isMatch($currentName, self::AT_NAMING_REGEX . '');
    }
}
