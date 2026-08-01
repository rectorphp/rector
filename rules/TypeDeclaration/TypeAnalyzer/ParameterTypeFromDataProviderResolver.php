<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use RectorPrefix202608\Webmozart\Assert\Assert;
final class ParameterTypeFromDataProviderResolver
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    public function __construct(NodeTypeResolver $nodeTypeResolver, BetterNodeFinder $betterNodeFinder, TypeFactory $typeFactory)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->typeFactory = $typeFactory;
    }
    /**
     * @param ClassMethod[] $dataProviderClassMethods
     */
    public function resolve(int $parameterPosition, array $dataProviderClassMethods): Type
    {
        Assert::allIsInstanceOf($dataProviderClassMethods, ClassMethod::class);
        $paramTypes = [];
        foreach ($dataProviderClassMethods as $dataProviderClassMethod) {
            $paramTypes[] = $this->resolveParameterTypeFromDataProvider($parameterPosition, $dataProviderClassMethod);
        }
        return TypeCombinator::union(...$paramTypes);
    }
    private function resolveParameterTypeFromDataProvider(int $parameterPosition, ClassMethod $dataProviderClassMethod): Type
    {
        $returns = $this->betterNodeFinder->findReturnsScoped($dataProviderClassMethod);
        if ($returns !== []) {
            return $this->resolveReturnStaticArrayTypeByParameterPosition($returns, $parameterPosition);
        }
        $yieldFromNodes = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($dataProviderClassMethod, YieldFrom::class);
        // "yield from" data sets are not resolved here → the resolved type would be incomplete
        if ($yieldFromNodes !== []) {
            return new MixedType();
        }
        /** @var Yield_[] $yields */
        $yields = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($dataProviderClassMethod, Yield_::class);
        return $this->resolveYieldStaticArrayTypeByParameterPosition($yields, $parameterPosition);
    }
    /**
     * @param Return_[] $returns
     */
    private function resolveReturnStaticArrayTypeByParameterPosition(array $returns, int $parameterPosition): Type
    {
        $firstReturnedExpr = $returns[0]->expr;
        if (!$firstReturnedExpr instanceof Array_) {
            return new MixedType();
        }
        $paramOnPositionTypes = $this->resolveParamOnPositionTypes($firstReturnedExpr, $parameterPosition);
        if ($paramOnPositionTypes === []) {
            return new MixedType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($paramOnPositionTypes);
    }
    /**
     * @param Yield_[] $yields
     */
    private function resolveYieldStaticArrayTypeByParameterPosition(array $yields, int $parameterPosition): Type
    {
        $paramOnPositionTypes = [];
        foreach ($yields as $yield) {
            if (!$yield->value instanceof Expr) {
                return new MixedType();
            }
            $constantArrayTypes = $this->resolveYieldedConstantArrayTypes($yield->value);
            // one of the yielded data sets cannot be resolved → the resolved type would be incomplete
            if ($constantArrayTypes === []) {
                return new MixedType();
            }
            foreach ($constantArrayTypes as $constantArrayType) {
                foreach ($constantArrayType->getValueTypes() as $position => $valueType) {
                    if ($position !== $parameterPosition) {
                        continue;
                    }
                    $paramOnPositionTypes[] = $valueType;
                }
            }
        }
        if ($paramOnPositionTypes === []) {
            return new MixedType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($paramOnPositionTypes);
    }
    /**
     * @return ConstantArrayType[]
     */
    private function resolveYieldedConstantArrayTypes(Expr $expr): array
    {
        $yieldedType = $this->nodeTypeResolver->getType($expr);
        if ($yieldedType instanceof ConstantArrayType) {
            return [$yieldedType];
        }
        if (!$yieldedType instanceof UnionType) {
            return [];
        }
        $constantArrayTypes = [];
        foreach ($yieldedType->getTypes() as $unionedType) {
            // impossible to resolve
            if (!$unionedType instanceof ConstantArrayType) {
                return [];
            }
            $constantArrayTypes[] = $unionedType;
        }
        return $constantArrayTypes;
    }
    /**
     * @return Type[]
     */
    private function resolveParamOnPositionTypes(Array_ $array, int $parameterPosition): array
    {
        $paramOnPositionTypes = [];
        foreach ($array->items as $singleDataProvidedSet) {
            if (!$singleDataProvidedSet instanceof ArrayItem || !$singleDataProvidedSet->value instanceof Array_) {
                return [];
            }
            foreach ($singleDataProvidedSet->value->items as $position => $singleDataProvidedSetItem) {
                if ($position !== $parameterPosition) {
                    continue;
                }
                if (!$singleDataProvidedSetItem instanceof ArrayItem) {
                    continue;
                }
                $paramOnPositionTypes[] = $this->nodeTypeResolver->getType($singleDataProvidedSetItem->value);
            }
        }
        return $paramOnPositionTypes;
    }
}
