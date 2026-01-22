<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\FunctionLike;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\StrictMixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\FileNode;
use Rector\PHPStan\ScopeFetcher;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class StrictTypeSafetyChecker
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeTypeResolver $nodeTypeResolver, ReflectionResolver $reflectionResolver, StaticTypeMapper $staticTypeMapper)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function isFileStrictTypeSafe(FileNode $fileNode): bool
    {
        $callLikes = $this->betterNodeFinder->findInstanceOf($fileNode->stmts, CallLike::class);
        foreach ($callLikes as $callLike) {
            if (!$this->isCallLikeSafe($callLike)) {
                return \false;
            }
        }
        $attributes = $this->betterNodeFinder->findInstanceOf($fileNode->stmts, Attribute::class);
        foreach ($attributes as $attribute) {
            if (!$this->isAttributeSafe($attribute)) {
                return \false;
            }
        }
        $functionLikes = $this->betterNodeFinder->findInstanceOf($fileNode->stmts, FunctionLike::class);
        foreach ($functionLikes as $functionLike) {
            if (!$this->areFunctionReturnsTypeSafe($functionLike)) {
                return \false;
            }
        }
        $assigns = $this->betterNodeFinder->findInstanceOf($fileNode->stmts, Assign::class);
        foreach ($assigns as $assign) {
            if (!$this->isPropertyAssignSafe($assign)) {
                return \false;
            }
        }
        return \true;
    }
    private function isCallLikeSafe(CallLike $callLike): bool
    {
        if ($callLike->isFirstClassCallable()) {
            return \true;
        }
        $reflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($callLike);
        if (!$reflection instanceof FunctionReflection && !$reflection instanceof MethodReflection) {
            return \false;
        }
        $scope = ScopeFetcher::fetch($callLike);
        $parameters = ParametersAcceptorSelectorVariantsWrapper::select($reflection, $callLike, $scope)->getParameters();
        return $this->areArgsSafe($callLike->getArgs(), $parameters);
    }
    private function isAttributeSafe(Attribute $attribute): bool
    {
        $reflection = $this->reflectionResolver->resolveConstructorReflectionFromAttribute($attribute);
        if (!$reflection instanceof MethodReflection) {
            return \false;
        }
        $parameters = ParametersAcceptorSelector::combineAcceptors($reflection->getVariants())->getParameters();
        return $this->areArgsSafe($attribute->args, $parameters);
    }
    /**
     * @param Arg[] $args
     * @param ParameterReflection[] $parameters
     */
    private function areArgsSafe(array $args, array $parameters): bool
    {
        foreach ($args as $position => $arg) {
            if ($arg->unpack) {
                return \false;
            }
            $parameterReflection = null;
            if ($arg->name !== null) {
                foreach ($parameters as $parameter) {
                    if ($parameter->getName() === $arg->name->name) {
                        $parameterReflection = $parameter;
                        break;
                    }
                }
            } elseif (isset($parameters[$position])) {
                $parameterReflection = $parameters[$position];
            } else {
                $lastParameter = end($parameters);
                if ($lastParameter !== \false && $lastParameter->isVariadic()) {
                    $parameterReflection = $lastParameter;
                }
            }
            if ($parameterReflection === null) {
                return \false;
            }
            $parameterType = $parameterReflection->getType();
            $argType = $this->nodeTypeResolver->getNativeType($arg->value);
            if (!$this->isTypeSafeForStrictMode($parameterType, $argType)) {
                return \false;
            }
        }
        return \true;
    }
    private function areFunctionReturnsTypeSafe(FunctionLike $functionLike): bool
    {
        if ($functionLike->getReturnType() === null) {
            return \true;
        }
        $declaredReturnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->getReturnType());
        if ($declaredReturnType instanceof MixedType || $declaredReturnType instanceof NeverType || $declaredReturnType->isVoid()->yes()) {
            return \true;
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($functionLike);
        foreach ($returns as $return) {
            if ($return->expr === null) {
                continue;
            }
            $returnExprType = $this->nodeTypeResolver->getNativeType($return->expr);
            if (!$this->isTypeSafeForStrictMode($declaredReturnType, $returnExprType)) {
                return \false;
            }
        }
        return \true;
    }
    private function isPropertyAssignSafe(Assign $assign): bool
    {
        if (!$assign->var instanceof PropertyFetch && !$assign->var instanceof StaticPropertyFetch) {
            return \true;
        }
        $propertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($assign->var);
        if (!$propertyReflection instanceof PhpPropertyReflection) {
            return \false;
        }
        $propertyType = $propertyReflection->getNativeType();
        $assignedType = $this->nodeTypeResolver->getNativeType($assign->expr);
        return $this->isTypeSafeForStrictMode($propertyType, $assignedType);
    }
    private function isTypeSafeForStrictMode(Type $declaredType, Type $valueType): bool
    {
        // need to be strict with mixed to avoid false positives
        if ($valueType instanceof MixedType) {
            $valueType = new StrictMixedType();
        }
        return $declaredType->accepts($valueType, \true)->yes();
    }
}
