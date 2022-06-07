<?php

declare (strict_types=1);
namespace Rector\Privatization\TypeManipulator;

use PhpParser\Node;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeNameResolver\NodeNameResolver;
final class NormalizeTypeToRespectArrayScalarType
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function normalizeToArray(Type $type, ?Node $returnNode) : Type
    {
        if ($returnNode === null) {
            return $type;
        }
        if (!$this->nodeNameResolver->isName($returnNode, 'array')) {
            return $type;
        }
        if ($type instanceof UnionType) {
            return $this->normalizeUnionType($type);
        }
        if ($type instanceof MixedType) {
            return new ArrayType($type, $type);
        }
        if ($type instanceof ArrayType) {
            return $this->resolveArrayType($type);
        }
        return $type;
    }
    private function resolveArrayType(ArrayType $arrayType) : ArrayType
    {
        $itemType = $arrayType->getItemType();
        if (!$itemType instanceof IntersectionType) {
            return $arrayType;
        }
        $types = $itemType->getTypes();
        foreach ($types as $key => $itemTypeType) {
            if ($itemTypeType instanceof NonEmptyArrayType) {
                unset($types[$key]);
            }
        }
        $arrayItemType = \count($types) === 1 ? \array_pop($types) : new IntersectionType($types);
        return new ArrayType($arrayType->getKeyType(), $arrayItemType);
    }
    private function normalizeUnionType(UnionType $unionType) : UnionType
    {
        $normalizedTypes = [];
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof MixedType) {
                $normalizedTypes[] = new ArrayType($unionedType, $unionedType);
                continue;
            }
            $normalizedTypes[] = $unionedType;
        }
        if ($unionType->getTypes() === $normalizedTypes) {
            return $unionType;
        }
        return new UnionType($normalizedTypes);
    }
}
