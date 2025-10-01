<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use RectorPrefix202510\Webmozart\Assert\Assert;
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
            if (!$yield->value instanceof Array_) {
                continue;
            }
            $type = $this->getTypeFromClassMethodYield($yield->value);
            if (!$type instanceof ConstantArrayType) {
                return $type;
            }
            foreach ($type->getValueTypes() as $position => $valueType) {
                if ($position !== $parameterPosition) {
                    continue;
                }
                $paramOnPositionTypes[] = $valueType;
            }
        }
        if ($paramOnPositionTypes === []) {
            return new MixedType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($paramOnPositionTypes);
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\Constant\ConstantArrayType
     */
    private function getTypeFromClassMethodYield(Array_ $classMethodYieldArray)
    {
        $arrayType = $this->nodeTypeResolver->getType($classMethodYieldArray);
        // impossible to resolve
        if (!$arrayType instanceof ConstantArrayType) {
            return new MixedType();
        }
        return $arrayType;
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
