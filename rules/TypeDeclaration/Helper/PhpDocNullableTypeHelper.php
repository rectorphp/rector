<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Helper;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Param;
use PHPStan\Type\ClosureType;
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
    public function __construct(StaticTypeMapper $staticTypeMapper, ValueResolver $valueResolver)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->valueResolver = $valueResolver;
    }
    /**
     * @return Type|null Returns null if it was not possible to resolve new php doc type or if update is not required
     */
    public function resolveUpdatedPhpDocTypeFromPhpDocTypeAndPhpParserType(Type $phpDocType, Type $phpParserType) : ?Type
    {
        if ($phpParserType instanceof MixedType) {
            return null;
        }
        return $this->resolveUpdatedPhpDocTypeFromPhpDocTypeAndPhpParserTypeNullInfo($phpDocType, $this->isParserTypeContainingNullType($phpParserType));
    }
    /**
     * @return Type|null Returns null if it was not possible to resolve new php doc param type or if update is not required
     */
    public function resolveUpdatedPhpDocTypeFromPhpDocTypeAndParamNode(Type $phpDocType, Param $param) : ?Type
    {
        if ($param->type === null) {
            return null;
        }
        $phpParserType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        if ($phpParserType instanceof UnionType) {
            $isPhpParserTypeContainingNullType = TypeCombinator::containsNull($phpParserType);
        } elseif ($param->default instanceof Expr) {
            $value = $this->valueResolver->getValue($param->default);
            $isPhpParserTypeContainingNullType = $value === null || $param->default instanceof ConstFetch && $value === 'null';
        } else {
            $isPhpParserTypeContainingNullType = \false;
        }
        $resolvedType = $this->resolveUpdatedPhpDocTypeFromPhpDocTypeAndPhpParserTypeNullInfo($phpDocType, $isPhpParserTypeContainingNullType);
        if ($resolvedType instanceof UnionType) {
            return $this->cleanNullableMixed($resolvedType);
        }
        if ($resolvedType instanceof Type) {
            return $resolvedType;
        }
        if (!$phpDocType instanceof UnionType) {
            return null;
        }
        $cleanNullableMixed = $this->cleanNullableMixed($phpDocType);
        if ($cleanNullableMixed === $phpDocType) {
            return null;
        }
        return $cleanNullableMixed;
    }
    /**
     * @param array<Type> $updatedDocTypes
     *
     * @return array<Type>
     */
    private function appendOrPrependNullTypeIfAppropriate(bool $isPhpParserTypeContainingNullType, bool $isPhpDocTypeContainingClosureType, array $updatedDocTypes) : array
    {
        if (!$isPhpParserTypeContainingNullType) {
            return $updatedDocTypes;
        }
        if ($isPhpDocTypeContainingClosureType) {
            \array_unshift($updatedDocTypes, new NullType());
        } else {
            $updatedDocTypes[] = new NullType();
        }
        return $updatedDocTypes;
    }
    private function hasClosureType(Type $phpDocType) : bool
    {
        if ($phpDocType instanceof ClosureType) {
            return \true;
        }
        if ($phpDocType instanceof UnionType) {
            foreach ($phpDocType->getTypes() as $subType) {
                if ($subType instanceof ClosureType) {
                    return \true;
                }
            }
        }
        return \false;
    }
    private function hasNullType(Type $phpDocType) : bool
    {
        if ($phpDocType instanceof UnionType) {
            return TypeCombinator::containsNull($phpDocType);
        }
        return \false;
    }
    /**
     * @return Type[]
     */
    private function resolveUpdatedDocTypes(Type $phpDocType) : array
    {
        $updatedDocTypes = [];
        if ($phpDocType instanceof UnionType) {
            foreach ($phpDocType->getTypes() as $subType) {
                if ($subType instanceof NullType) {
                    continue;
                }
                $updatedDocTypes[] = $subType;
            }
        } else {
            $updatedDocTypes[] = $phpDocType;
        }
        return $updatedDocTypes;
    }
    private function cleanNullableMixed(UnionType $unionType) : Type
    {
        if (!TypeCombinator::containsNull($unionType)) {
            return $unionType;
        }
        $types = $unionType->getTypes();
        foreach ($types as $type) {
            if ($type instanceof MixedType) {
                return TypeCombinator::removeNull($unionType);
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
    private function composeUpdatedPhpDocType(array $updatedDocTypes) : Type
    {
        return \count($updatedDocTypes) === 1 ? $updatedDocTypes[0] : new UnionType($updatedDocTypes);
    }
    private function isParserTypeContainingNullType(Type $phpParserType) : bool
    {
        if ($phpParserType instanceof UnionType) {
            return TypeCombinator::containsNull($phpParserType);
        }
        return \false;
    }
    private function resolveUpdatedPhpDocTypeFromPhpDocTypeAndPhpParserTypeNullInfo(Type $phpDocType, bool $isPhpParserTypeContainingNullType) : ?Type
    {
        $isPhpDocTypeContainingNullType = $this->hasNullType($phpDocType);
        $isPhpDocTypeContainingClosureType = $this->hasClosureType($phpDocType);
        $updatedDocTypes = $this->resolveUpdatedDocTypes($phpDocType);
        if (!$this->isItRequiredToRemoveOrAddNullTypeToUnion($isPhpDocTypeContainingNullType, $isPhpParserTypeContainingNullType)) {
            return null;
        }
        $updatedDocTypes = $this->appendOrPrependNullTypeIfAppropriate($isPhpParserTypeContainingNullType, $isPhpDocTypeContainingClosureType, $updatedDocTypes);
        return $this->composeUpdatedPhpDocType($updatedDocTypes);
    }
}
