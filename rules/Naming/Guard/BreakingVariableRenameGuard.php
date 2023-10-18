<?php

declare (strict_types=1);
namespace Rector\Naming\Guard;

use DateTimeInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Error;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
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
    /**
     * @var string
     * @see https://regex101.com/r/1pKLgf/1
     */
    public const AT_NAMING_REGEX = '#[\\w+]At$#';
    public function __construct(BetterNodeFinder $betterNodeFinder, ConflictingNameResolver $conflictingNameResolver, NodeTypeResolver $nodeTypeResolver, OverridenExistingNamesResolver $overridenExistingNamesResolver, TypeUnwrapper $typeUnwrapper, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->conflictingNameResolver = $conflictingNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->overridenExistingNamesResolver = $overridenExistingNamesResolver;
        $this->typeUnwrapper = $typeUnwrapper;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    public function shouldSkipVariable(string $currentName, string $expectedName, $functionLike, Variable $variable) : bool
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
        return $this->isUsedInClosureUsesName($expectedName, $functionLike);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $classMethod
     */
    public function shouldSkipParam(string $currentName, string $expectedName, $classMethod, Param $param) : bool
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
        if ($this->overridenExistingNamesResolver->hasNameInFunctionLikeForParam($expectedName, $classMethod)) {
            return \true;
        }
        if ($param->var instanceof Error) {
            return \true;
        }
        if ($this->isVariableAlreadyDefined($param->var, $currentName)) {
            return \true;
        }
        if ($this->isRamseyUuidInterface($param)) {
            return \true;
        }
        if ($this->isGenerator($param)) {
            return \true;
        }
        if ($this->isDateTimeAtNamingConvention($param)) {
            return \true;
        }
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->getStmts(), function (Node $node) use($expectedName) : bool {
            if (!$node instanceof Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $expectedName);
        });
    }
    private function isVariableAlreadyDefined(Variable $variable, string $currentVariableName) : bool
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
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function hasConflictVariable($functionLike, string $newName) : bool
    {
        return $this->betterNodeFinder->hasInstanceOfName((array) $functionLike->stmts, Variable::class, $newName);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function isUsedInClosureUsesName(string $expectedName, $functionLike) : bool
    {
        if (!$functionLike instanceof Closure) {
            return \false;
        }
        return $this->betterNodeFinder->hasVariableOfName($functionLike->uses, $expectedName);
    }
    private function isRamseyUuidInterface(Param $param) : bool
    {
        return $this->nodeTypeResolver->isObjectType($param, new ObjectType('Ramsey\\Uuid\\UuidInterface'));
    }
    private function isDateTimeAtNamingConvention(Param $param) : bool
    {
        $type = $this->nodeTypeResolver->getType($param);
        $type = $this->typeUnwrapper->unwrapFirstObjectTypeFromUnionType($type);
        if (!$type instanceof TypeWithClassName) {
            return \false;
        }
        if (!\is_a($type->getClassName(), DateTimeInterface::class, \true)) {
            return \false;
        }
        /** @var string $currentName */
        $currentName = $this->nodeNameResolver->getName($param);
        return StringUtils::isMatch($currentName, self::AT_NAMING_REGEX . '');
    }
    private function isGenerator(Param $param) : bool
    {
        if (!$param->type instanceof Node) {
            return \false;
        }
        $paramType = $this->nodeTypeResolver->getType($param);
        if (!$paramType instanceof ObjectType) {
            return \false;
        }
        if (\substr_compare($paramType->getClassName(), 'Generator', -\strlen('Generator')) === 0 || \substr_compare($paramType->getClassName(), 'Iterator', -\strlen('Iterator')) === 0) {
            return \true;
        }
        return $paramType->isInstanceOf('Symfony\\Component\\DependencyInjection\\Argument\\RewindableGenerator')->yes();
    }
}
