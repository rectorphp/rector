<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Param;
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
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver
     */
    private $classMethodParamVendorLockResolver;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeCommonTypeNarrower
     */
    private $unionTypeCommonTypeNarrower;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver $classMethodParamVendorLockResolver, \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeCommonTypeNarrower $unionTypeCommonTypeNarrower, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->classMethodParamVendorLockResolver = $classMethodParamVendorLockResolver;
        $this->unionTypeCommonTypeNarrower = $unionTypeCommonTypeNarrower;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    /**
     * @param array<int, Type> $classParameterTypes
     */
    public function complete(\PhpParser\Node\Stmt\ClassMethod $classMethod, array $classParameterTypes, int $maxUnionTypes) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $hasChanged = \false;
        foreach ($classParameterTypes as $position => $argumentStaticType) {
            /** @var Type $argumentStaticType */
            if ($this->shouldSkipArgumentStaticType($classMethod, $argumentStaticType, $position, $maxUnionTypes)) {
                continue;
            }
            $phpParserTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($argumentStaticType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PARAM);
            if (!$phpParserTypeNode instanceof \PhpParser\Node) {
                continue;
            }
            // check default override
            $param = $classMethod->params[$position];
            if (!$this->isAcceptedByDefault($param, $argumentStaticType)) {
                continue;
            }
            // update parameter
            $param->type = $phpParserTypeNode;
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $classMethod;
        }
        return null;
    }
    private function shouldSkipArgumentStaticType(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Type\Type $argumentStaticType, int $position, int $maxUnionTypes) : bool
    {
        if ($argumentStaticType instanceof \PHPStan\Type\MixedType) {
            return \true;
        }
        // skip mixed in union type
        if ($argumentStaticType instanceof \PHPStan\Type\UnionType && $argumentStaticType->isSuperTypeOf(new \PHPStan\Type\MixedType())->yes()) {
            return \true;
        }
        if (!isset($classMethod->params[$position])) {
            return \true;
        }
        if ($this->classMethodParamVendorLockResolver->isVendorLocked($classMethod)) {
            return \true;
        }
        $parameter = $classMethod->params[$position];
        if ($parameter->type === null) {
            return \false;
        }
        $currentParameterStaticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($parameter->type);
        if ($this->isClosureAndCallableType($currentParameterStaticType, $argumentStaticType)) {
            return \true;
        }
        // narrow union type in case its not supported yet
        $argumentStaticType = $this->narrowUnionTypeIfNotSupported($argumentStaticType);
        // too many union types
        if ($this->isTooDetailedUnionType($currentParameterStaticType, $argumentStaticType, $maxUnionTypes)) {
            return \true;
        }
        // current type already accepts the one added
        if ($currentParameterStaticType->accepts($argumentStaticType, \true)->yes()) {
            return \true;
        }
        // avoid overriding more precise type
        if ($argumentStaticType->isSuperTypeOf($currentParameterStaticType)->yes()) {
            return \true;
        }
        // already completed → skip
        return $currentParameterStaticType->equals($argumentStaticType);
    }
    private function isClosureAndCallableType(\PHPStan\Type\Type $parameterStaticType, \PHPStan\Type\Type $argumentStaticType) : bool
    {
        if ($parameterStaticType instanceof \PHPStan\Type\CallableType && $this->isClosureObjectType($argumentStaticType)) {
            return \true;
        }
        return $argumentStaticType instanceof \PHPStan\Type\CallableType && $this->isClosureObjectType($parameterStaticType);
    }
    private function isClosureObjectType(\PHPStan\Type\Type $type) : bool
    {
        if (!$type instanceof \PHPStan\Type\ObjectType) {
            return \false;
        }
        return $type->getClassName() === 'Closure';
    }
    private function isTooDetailedUnionType(\PHPStan\Type\Type $currentType, \PHPStan\Type\Type $newType, int $maxUnionTypes) : bool
    {
        if ($currentType instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        if (!$newType instanceof \PHPStan\Type\UnionType) {
            return \false;
        }
        return \count($newType->getTypes()) > $maxUnionTypes;
    }
    private function narrowUnionTypeIfNotSupported(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        if (!$type instanceof \PHPStan\Type\UnionType) {
            return $type;
        }
        // union is supported, so it's ok
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::UNION_TYPES)) {
            return $type;
        }
        $narrowedObjectType = $this->unionTypeCommonTypeNarrower->narrowToSharedObjectType($type);
        if ($narrowedObjectType instanceof \PHPStan\Type\ObjectType) {
            return $narrowedObjectType;
        }
        return $type;
    }
    private function isAcceptedByDefault(\PhpParser\Node\Param $param, \PHPStan\Type\Type $argumentStaticType) : bool
    {
        if (!$param->default instanceof \PhpParser\Node\Expr) {
            return \true;
        }
        $defaultExpr = $param->default;
        $defaultStaticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($defaultExpr);
        return $argumentStaticType->accepts($defaultStaticType, \false)->yes();
    }
}
