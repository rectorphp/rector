<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\CallableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver;

final class ClassMethodParamTypeCompleter
{
    public function __construct(
        private StaticTypeMapper $staticTypeMapper,
        private ClassMethodParamVendorLockResolver $classMethodParamVendorLockResolver
    ) {
    }

    /**
     * @param array<int, Type> $classParameterTypes
     */
    public function complete(ClassMethod $classMethod, array $classParameterTypes): ?ClassMethod
    {
        $hasChanged = false;

        foreach ($classParameterTypes as $position => $argumentStaticType) {
            if ($this->shouldSkipArgumentStaticType($classMethod, $argumentStaticType, $position)) {
                continue;
            }

            $phpParserTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($argumentStaticType);
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
        int $position
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

        $parameterStaticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($parameter->type);

        if ($this->isClosureAndCallableType($parameterStaticType, $argumentStaticType)) {
            return true;
        }

        // already completed → skip
        return $parameterStaticType->equals($argumentStaticType);
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
}
