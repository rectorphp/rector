<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Type;

use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class TypeToStringResolver
{
    /**
     * @return string[]
     */
    public function resolve(Type $type): array
    {
        $types = [];

        if ($type instanceof ObjectType) {
            $types[] = $type->getClassName();
        }

        if ($type instanceof UnionType || $type instanceof IntersectionType) {
            foreach ($type->getTypes() as $type) {
                if ($type instanceof ObjectType) {
                    $types[] = $type->getClassName();
                }
            }
        }

        return $types;
    }
}
