<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Helper;

use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Param;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class PhpDocNullableTypeHelper
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->valueResolver = $valueResolver;
    }
    /**
     * @return Type|null Returns null if it was not possible to resolve new php doc type or if update is not required
     */
    public function resolveUpdatedPhpDocTypeFromPhpDocTypeAndPhpParserType(\PHPStan\Type\Type $phpDocType, \PHPStan\Type\Type $phpParserType) : ?\PHPStan\Type\Type
    {
        return $this->resolveUpdatedPhpDocTypeFromPhpDocTypeAndPhpParserTypeNullInfo($phpDocType, $this->isParserTypeContainingNullType($phpParserType));
    }
    /**
     * @return Type|null Returns null if it was not possible to resolve new php doc param type or if update is not required
     */
    public function resolveUpdatedPhpDocTypeFromPhpDocTypeAndParamNode(\PHPStan\Type\Type $phpDocType, \PhpParser\Node\Param $param) : ?\PHPStan\Type\Type
    {
        if ($param->type === null) {
            return null;
        }
        $phpParserType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        if ($phpParserType instanceof \PHPStan\Type\UnionType) {
            $isPhpParserTypeContainingNullType = \PHPStan\Type\TypeCombinator::containsNull($phpParserType);
        } elseif ($param->default !== null) {
            $value = $this->valueResolver->getValue($param->default);
            $isPhpParserTypeContainingNullType = $value === null || $param->default instanceof \PhpParser\Node\Expr\ConstFetch && $value === 'null';
        } else {
            $isPhpParserTypeContainingNullType = \false;
        }
        $resolvedType = $this->resolveUpdatedPhpDocTypeFromPhpDocTypeAndPhpParserTypeNullInfo($phpDocType, $isPhpParserTypeContainingNullType);
        if ($resolvedType instanceof \PHPStan\Type\UnionType) {
            return $this->cleanNullableMixed($resolvedType);
        }
        if ($resolvedType instanceof \PHPStan\Type\Type) {
            return $resolvedType;
        }
        if (!$phpDocType instanceof \PHPStan\Type\UnionType) {
            return null;
        }
        $cleanNullableMixed = $this->cleanNullableMixed($phpDocType);
        if ($cleanNullableMixed === $phpDocType) {
            return null;
        }
        return $cleanNullableMixed;
    }
    private function cleanNullableMixed(\PHPStan\Type\UnionType $unionType) : \PHPStan\Type\Type
    {
        if (!\PHPStan\Type\TypeCombinator::containsNull($unionType)) {
            return $unionType;
        }
        $types = $unionType->getTypes();
        foreach ($types as $type) {
            if ($type instanceof \PHPStan\Type\MixedType) {
                return \PHPStan\Type\TypeCombinator::removeNull($unionType);
            }
        }
        return $unionType;
    }
    private function isItRequiredToRemoveOrAddNullTypeToUnion(bool $phpDocTypeContainsNullType, bool $phpParserTypeContainsNullType) : bool
    {
        return $phpParserTypeContainsNullType && !$phpDocTypeContainsNullType || !$phpParserTypeContainsNullType && $phpDocTypeContainsNullType;
    }
    /**
     * @param Type[] $updatedDocTypes
     */
    private function composeUpdatedPhpDocType(array $updatedDocTypes) : \PHPStan\Type\Type
    {
        return \count($updatedDocTypes) === 1 ? $updatedDocTypes[0] : new \PHPStan\Type\UnionType($updatedDocTypes);
    }
    private function isParserTypeContainingNullType(\PHPStan\Type\Type $phpParserType) : bool
    {
        if ($phpParserType instanceof \PHPStan\Type\UnionType) {
            return \PHPStan\Type\TypeCombinator::containsNull($phpParserType);
        }
        return \false;
    }
    private function resolveUpdatedPhpDocTypeFromPhpDocTypeAndPhpParserTypeNullInfo(\PHPStan\Type\Type $phpDocType, bool $isPhpParserTypeContainingNullType) : ?\PHPStan\Type\Type
    {
        /** @var array<(NullType | UnionType)> $updatedDocTypes */
        $updatedDocTypes = [];
        $phpDocTypeContainsNullType = \false;
        if ($phpDocType instanceof \PHPStan\Type\UnionType) {
            $phpDocTypeContainsNullType = \PHPStan\Type\TypeCombinator::containsNull($phpDocType);
            foreach ($phpDocType->getTypes() as $subType) {
                if ($subType instanceof \PHPStan\Type\NullType) {
                    continue;
                }
                $updatedDocTypes[] = $subType;
            }
        } else {
            $updatedDocTypes[] = $phpDocType;
        }
        if (!$this->isItRequiredToRemoveOrAddNullTypeToUnion($phpDocTypeContainsNullType, $isPhpParserTypeContainingNullType)) {
            return null;
        }
        if ($isPhpParserTypeContainingNullType) {
            $updatedDocTypes[] = new \PHPStan\Type\NullType();
        }
        return $this->composeUpdatedPhpDocType($updatedDocTypes);
    }
}
