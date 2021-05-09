<?php

declare (strict_types=1);
namespace Rector\Privatization\TypeManipulator;

use PhpParser\Node;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeNameResolver\NodeNameResolver;
final class NormalizeTypeToRespectArrayScalarType
{
    /**
     * @var NodeNameResolver
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
        return $type;
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
