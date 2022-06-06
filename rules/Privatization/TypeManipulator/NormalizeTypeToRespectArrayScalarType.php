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
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function normalizeToArray(\PHPStan\Type\Type $type, ?\PhpParser\Node $returnNode) : \PHPStan\Type\Type
    {
        if ($returnNode === null) {
            return $type;
        }
        if (!$this->nodeNameResolver->isName($returnNode, 'array')) {
            return $type;
        }
        if ($type instanceof \PHPStan\Type\UnionType) {
            return $this->normalizeUnionType($type);
        }
        if ($type instanceof \PHPStan\Type\MixedType) {
            return new \PHPStan\Type\ArrayType($type, $type);
        }
        if ($type instanceof \PHPStan\Type\ArrayType) {
            return $this->resolveArrayType($type);
        }
        return $type;
    }
    private function resolveArrayType(\PHPStan\Type\ArrayType $arrayType) : \PHPStan\Type\ArrayType
    {
        $itemType = $arrayType->getItemType();
        if (!$itemType instanceof \PHPStan\Type\IntersectionType) {
            return $arrayType;
        }
        $types = $itemType->getTypes();
        foreach ($types as $key => $itemTypeType) {
            if ($itemTypeType instanceof \PHPStan\Type\Accessory\NonEmptyArrayType) {
                unset($types[$key]);
            }
        }
        $arrayItemType = \count($types) === 1 ? \array_pop($types) : new \PHPStan\Type\IntersectionType($types);
        return new \PHPStan\Type\ArrayType($arrayType->getKeyType(), $arrayItemType);
    }
    private function normalizeUnionType(\PHPStan\Type\UnionType $unionType) : \PHPStan\Type\UnionType
    {
        $normalizedTypes = [];
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof \PHPStan\Type\MixedType) {
                $normalizedTypes[] = new \PHPStan\Type\ArrayType($unionedType, $unionedType);
                continue;
            }
            $normalizedTypes[] = $unionedType;
        }
        if ($unionType->getTypes() === $normalizedTypes) {
            return $unionType;
        }
        return new \PHPStan\Type\UnionType($normalizedTypes);
    }
}
