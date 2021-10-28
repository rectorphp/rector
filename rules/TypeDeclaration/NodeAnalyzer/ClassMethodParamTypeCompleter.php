<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\CallableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeCommonTypeNarrower;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver;

final class ClassMethodParamTypeCompleter
{
    public function __construct(
        private StaticTypeMapper $staticTypeMapper,
        private ClassMethodParamVendorLockResolver $classMethodParamVendorLockResolver,
        private UnionTypeCommonTypeNarrower $unionTypeCommonTypeNarrower,
        private PhpVersionProvider $phpVersionProvider,
    ) {
    }

    /**
     * @param array<int, Type> $classParameterTypes
     */
    public function complete(ClassMethod $classMethod, array $classParameterTypes, int $maxUnionTypes): ?ClassMethod
    {
        $hasChanged = false;

        foreach ($classParameterTypes as $position => $argumentStaticType) {
            if ($this->shouldSkipArgumentStaticType($classMethod, $argumentStaticType, $position, $maxUnionTypes)) {
                continue;
            }

            $phpParserTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
                $argumentStaticType,
                TypeKind::PARAM()
            );

            if (! $phpParserTypeNode instanceof Node) {
                continue;
            }

            // update parameter
            $classMethod->params[$position]->type = $phpParserTypeNode;
            $hasChanged = true;
        }

        if ($hasChanged) {
            return $classMethod;
        }

        return null;
    }

    private function shouldSkipArgumentStaticType(
        ClassMethod $classMethod,
        Type $argumentStaticType,
        int $position,
        int $maxUnionTypes
    ): bool {
        if ($argumentStaticType instanceof MixedType) {
            return true;
        }

        if (! isset($classMethod->params[$position])) {
            return true;
        }

        if ($this->classMethodParamVendorLockResolver->isVendorLocked($classMethod)) {
            return true;
        }

        $parameter = $classMethod->params[$position];
        if ($parameter->type === null) {
            return false;
        }

        $currentParameterStaticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($parameter->type);
        if ($this->isClosureAndCallableType($currentParameterStaticType, $argumentStaticType)) {
            return true;
        }

        // narrow union type in case its not supported yet
        $argumentStaticType = $this->narrowUnionTypeIfNotSupported($argumentStaticType);

        // too many union types
        if ($this->isTooDetailedUnionType($currentParameterStaticType, $argumentStaticType, $maxUnionTypes)) {
            return true;
        }

        // avoid overriding more precise type
        if ($argumentStaticType->isSuperTypeOf($currentParameterStaticType)->yes()) {
            return true;
        }

        // already completed → skip
        return $currentParameterStaticType->equals($argumentStaticType);
    }

    private function isClosureAndCallableType(Type $parameterStaticType, Type $argumentStaticType): bool
    {
        if ($parameterStaticType instanceof CallableType && $this->isClosureObjectType($argumentStaticType)) {
            return true;
        }

        return $argumentStaticType instanceof CallableType && $this->isClosureObjectType($parameterStaticType);
    }

    private function isClosureObjectType(Type $type): bool
    {
        if (! $type instanceof ObjectType) {
            return false;
        }

        return $type->getClassName() === 'Closure';
    }

    private function isTooDetailedUnionType(Type $currentType, Type $newType, int $maxUnionTypes): bool
    {
        if ($currentType instanceof MixedType) {
            return false;
        }

        if (! $newType instanceof UnionType) {
            return false;
        }

        return count($newType->getTypes()) > $maxUnionTypes;
    }

    private function narrowUnionTypeIfNotSupported(Type $type): Type
    {
        if (! $type instanceof UnionType) {
            return $type;
        }

        // union is supported, so it's ok
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES)) {
            return $type;
        }

        $narrowedObjectType = $this->unionTypeCommonTypeNarrower->narrowToSharedObjectType($type);
        if ($narrowedObjectType instanceof ObjectType) {
            return $narrowedObjectType;
        }

        return $type;
    }
}
