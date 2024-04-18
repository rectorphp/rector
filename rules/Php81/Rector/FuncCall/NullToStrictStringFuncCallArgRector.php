<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Cast\String_ as CastString_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Reflection\Native\NativeParameterWithPhpDocsReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\Php81\Enum\NameNullToStrictNullFunctionMap;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector\NullToStrictStringFuncCallArgRectorTest
 */
final class NullToStrictStringFuncCallArgRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ReflectionResolver $reflectionResolver, ArgsAnalyzer $argsAnalyzer, PropertyFetchAnalyzer $propertyFetchAnalyzer, ValueResolver $valueResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->argsAnalyzer = $argsAnalyzer;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change null to strict string defined function call args', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_split("#a#", null);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_split("#a#", '');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return null;
        }
        $args = $node->getArgs();
        $positions = $this->argsAnalyzer->hasNamedArg($args) ? $this->resolveNamedPositions($node, $args) : $this->resolveOriginalPositions($node, $scope);
        if ($positions === []) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        $isTrait = $classReflection instanceof ClassReflection && $classReflection->isTrait();
        $functionReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        if (!$functionReflection instanceof FunctionReflection) {
            return null;
        }
        $parametersAcceptor = ParametersAcceptorSelectorVariantsWrapper::select($functionReflection, $node, $scope);
        $isChanged = \false;
        foreach ($positions as $position) {
            $result = $this->processNullToStrictStringOnNodePosition($node, $args, $position, $isTrait, $scope, $parametersAcceptor);
            if ($result instanceof Node) {
                $node = $result;
                $isChanged = \true;
            }
        }
        if ($isChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_NULL_ARG_IN_STRING_FUNCTION;
    }
    /**
     * @param Arg[] $args
     * @return int[]|string[]
     */
    private function resolveNamedPositions(FuncCall $funcCall, array $args) : array
    {
        $functionName = $this->nodeNameResolver->getName($funcCall);
        $argNames = NameNullToStrictNullFunctionMap::FUNCTION_TO_PARAM_NAMES[$functionName];
        $positions = [];
        foreach ($args as $position => $arg) {
            if (!$arg->name instanceof Identifier) {
                continue;
            }
            if (!$this->nodeNameResolver->isNames($arg->name, $argNames)) {
                continue;
            }
            $positions[] = $position;
        }
        return $positions;
    }
    /**
     * @param Arg[] $args
     * @param int|string $position
     */
    private function processNullToStrictStringOnNodePosition(FuncCall $funcCall, array $args, $position, bool $isTrait, Scope $scope, ParametersAcceptor $parametersAcceptor) : ?FuncCall
    {
        if (!isset($args[$position])) {
            return null;
        }
        $argValue = $args[$position]->value;
        if ($argValue instanceof ConstFetch && $this->valueResolver->isNull($argValue)) {
            $args[$position]->value = new String_('');
            $funcCall->args = $args;
            return $funcCall;
        }
        $type = $this->nodeTypeResolver->getType($argValue);
        if ($type->isString()->yes()) {
            return null;
        }
        $nativeType = $this->nodeTypeResolver->getNativeType($argValue);
        if ($nativeType->isString()->yes()) {
            return null;
        }
        if ($this->shouldSkipType($type)) {
            return null;
        }
        if ($argValue instanceof Encapsed) {
            return null;
        }
        if ($this->isAnErrorType($argValue, $nativeType, $scope)) {
            return null;
        }
        if ($this->shouldSkipTrait($argValue, $type, $isTrait)) {
            return null;
        }
        $parameter = $parametersAcceptor->getParameters()[$position] ?? null;
        if ($parameter instanceof NativeParameterWithPhpDocsReflection && $parameter->getType() instanceof UnionType) {
            $parameterType = $parameter->getType();
            if (!$this->isValidUnionType($parameterType)) {
                return null;
            }
        }
        $args[$position]->value = new CastString_($argValue);
        $funcCall->args = $args;
        return $funcCall;
    }
    private function isValidUnionType(Type $type) : bool
    {
        if (!$type instanceof UnionType) {
            return \false;
        }
        foreach ($type->getTypes() as $childType) {
            if ($childType->isString()->yes()) {
                continue;
            }
            if ($childType->isNull()->yes()) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    private function shouldSkipType(Type $type) : bool
    {
        return !$type instanceof MixedType && !$type instanceof NullType && !$this->isValidUnionType($type);
    }
    private function shouldSkipTrait(Expr $expr, Type $type, bool $isTrait) : bool
    {
        if (!$type instanceof MixedType) {
            return \false;
        }
        if (!$isTrait) {
            return \false;
        }
        if ($type->isExplicitMixed()) {
            return \false;
        }
        if (!$expr instanceof MethodCall) {
            return $this->propertyFetchAnalyzer->isLocalPropertyFetch($expr);
        }
        return \true;
    }
    private function isAnErrorType(Expr $expr, Type $type, Scope $scope) : bool
    {
        if ($type instanceof ErrorType) {
            return \true;
        }
        $parentScope = $scope->getParentScope();
        if ($parentScope instanceof Scope) {
            return $parentScope->getType($expr) instanceof ErrorType;
        }
        return \false;
    }
    /**
     * @return int[]|string[]
     */
    private function resolveOriginalPositions(FuncCall $funcCall, Scope $scope) : array
    {
        $functionReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($funcCall);
        if (!$functionReflection instanceof NativeFunctionReflection) {
            return [];
        }
        $parametersAcceptor = ParametersAcceptorSelectorVariantsWrapper::select($functionReflection, $funcCall, $scope);
        $functionName = $functionReflection->getName();
        $argNames = NameNullToStrictNullFunctionMap::FUNCTION_TO_PARAM_NAMES[$functionName];
        $positions = [];
        foreach ($parametersAcceptor->getParameters() as $position => $parameterReflection) {
            if (\in_array($parameterReflection->getName(), $argNames, \true)) {
                $positions[] = $position;
            }
        }
        return $positions;
    }
    private function shouldSkip(FuncCall $funcCall) : bool
    {
        $functionNames = \array_keys(NameNullToStrictNullFunctionMap::FUNCTION_TO_PARAM_NAMES);
        if (!$this->nodeNameResolver->isNames($funcCall, $functionNames)) {
            return \true;
        }
        return $funcCall->isFirstClassCallable();
    }
}
