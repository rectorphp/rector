<?php

declare (strict_types=1);
namespace Rector\Php81\NodeManipulator;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Cast\Int_ as CastInt_;
use PhpParser\Node\Expr\Cast\String_ as CastString_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Native\ExtendedNativeParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
final class NullToStrictStringIntConverter
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private PropertyFetchAnalyzer $propertyFetchAnalyzer;
    public function __construct(ValueResolver $valueResolver, NodeTypeResolver $nodeTypeResolver, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->valueResolver = $valueResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    /**
     * @param Arg[] $args
     */
    public function convertIfNull(FuncCall $funcCall, array $args, int $position, bool $isTrait, Scope $scope, ParametersAcceptor $parametersAcceptor, string $targetType = 'string'): ?FuncCall
    {
        if (!isset($args[$position])) {
            return null;
        }
        $argValue = $args[$position]->value;
        if ($this->valueResolver->isNull($argValue)) {
            $args[$position]->value = $targetType === 'string' ? new String_('') : new Int_(0);
            $funcCall->args = $args;
            return $funcCall;
        }
        // skip (string) ternary conditions with both values
        if ($this->isStringCastedTernaryOfMixedTypes($argValue, $scope)) {
            return null;
        }
        if ($this->shouldSkipValue($argValue, $scope, $isTrait, $targetType)) {
            return null;
        }
        $parameterReflection = $parametersAcceptor->getParameters()[$position] ?? null;
        if ($parameterReflection instanceof ExtendedNativeParameterReflection && $parameterReflection->getType() instanceof UnionType) {
            $parameterType = $parameterReflection->getType();
            if (!$this->isValidUnionType($parameterType)) {
                return null;
            }
        }
        if ($argValue instanceof Ternary && !$this->shouldSkipValue($argValue->else, $scope, $isTrait, $targetType)) {
            if ($this->valueResolver->isNull($argValue->else)) {
                $argValue->else = $targetType === 'string' ? new String_('') : new Int_(0);
            } else {
                $argValue->else = $targetType === 'string' ? new CastString_($argValue->else) : new CastInt_($argValue->else);
            }
            $args[$position]->value = $argValue;
            return $funcCall;
        }
        $wrapInParentheses = \false;
        if ($argValue instanceof Ternary && $argValue->cond instanceof CastString_) {
            $wrapInParentheses = \true;
        }
        if ($targetType === 'string') {
            $castedType = new CastString_($argValue);
        } else {
            $castedType = new CastInt_($argValue);
        }
        if ($wrapInParentheses) {
            $argValue->setAttribute(AttributeKey::WRAPPED_IN_PARENTHESES, \true);
        }
        $args[$position]->value = $castedType;
        return $funcCall;
    }
    private function shouldSkipValue(Expr $expr, Scope $scope, bool $isTrait, string $targetType): bool
    {
        $type = $this->nodeTypeResolver->getType($expr);
        if ($type->isString()->yes() && $targetType === 'string') {
            return \true;
        }
        if ($type->isInteger()->yes() && $targetType === 'int') {
            return \true;
        }
        $nativeType = $this->nodeTypeResolver->getNativeType($expr);
        if ($nativeType->isString()->yes() && $targetType === 'string') {
            return \true;
        }
        if ($nativeType->isInteger()->yes() && $targetType === 'int') {
            return \true;
        }
        if ($this->isPossibleArrayVariableName($type, $nativeType, $expr)) {
            return \true;
        }
        if ($this->shouldSkipType($type)) {
            return \true;
        }
        if ($expr instanceof InterpolatedString) {
            return \true;
        }
        if ($this->isAnErrorType($expr, $nativeType, $scope)) {
            return \true;
        }
        return $this->shouldSkipTrait($expr, $type, $isTrait);
    }
    private function isValidUnionType(Type $type): bool
    {
        if (!$type instanceof UnionType) {
            return \false;
        }
        foreach ($type->getTypes() as $childType) {
            if ($childType->isString()->yes()) {
                continue;
            }
            if ($childType->isInteger()->yes()) {
                continue;
            }
            if ($childType->isNull()->yes()) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    private function shouldSkipType(Type $type): bool
    {
        return !$type instanceof MixedType && !$type->isNull()->yes() && !$this->isValidUnionType($type);
    }
    private function shouldSkipTrait(Expr $expr, Type $type, bool $isTrait): bool
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
    private function isAnErrorType(Expr $expr, Type $type, Scope $scope): bool
    {
        if ($type instanceof ErrorType) {
            return \true;
        }
        $parentScope = $scope->getParentScope();
        if ($parentScope instanceof Scope) {
            return $parentScope->getType($expr) instanceof ErrorType;
        }
        return $type instanceof MixedType && !$type->isExplicitMixed() && $type->getSubtractedType() instanceof NullType;
    }
    /**
     * @see https://github.com/rectorphp/rector/issues/9447 for context
     */
    private function isPossibleArrayVariableName(Type $passedType, Type $reflectionParamType, Expr $expr): bool
    {
        // could mixed, resp. array, no need to (string) cast array
        if (!$passedType instanceof MixedType) {
            return \false;
        }
        if (!$reflectionParamType->isArray()->maybe()) {
            return \false;
        }
        if ($expr instanceof Variable && is_string($expr->name)) {
            $variableName = $expr->name;
            // most likely plural variable
            return strlen($variableName) > 3 && substr_compare($variableName, 's', -strlen('s')) === 0;
        }
        return \false;
    }
    private function isStringCastedTernaryOfMixedTypes(Expr $expr, Scope $scope): bool
    {
        if (!$expr instanceof Ternary) {
            return \false;
        }
        if (!$expr->cond instanceof CastString_) {
            return \false;
        }
        if (!$expr->if instanceof Expr) {
            return \false;
        }
        $ifType = $scope->getType($expr->if);
        $elseType = $scope->getType($expr->else);
        return $ifType instanceof MixedType || $elseType instanceof MixedType;
    }
}
