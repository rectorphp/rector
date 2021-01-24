<?php

declare(strict_types=1);

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

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function normalizeToArray(Type $type, ?Node $returnNode): Type
    {
        if ($returnNode === null) {
            return $type;
        }

        if (! $this->nodeNameResolver->isName($returnNode, 'array')) {
            return $type;
        }

        if ($type instanceof UnionType) {
            return $this->normalizeUnionType($type);
        }

        if ($type instanceof MixedType) {
            return new ArrayType($type, $type);
        }

        return $type;
    }

    private function normalizeUnionType(UnionType $unionType): UnionType
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
