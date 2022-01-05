<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeAnalyzer;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IterableType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class IterableTypeAnalyzer
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function isIterableType(Type $type): bool
    {
        if ($type instanceof UnionType) {
            foreach ($type->getTypes() as $unionedType) {
                if (! $this->isIterableType($unionedType)) {
                    return false;
                }
            }

            return true;
        }

        if ($type instanceof ArrayType) {
            return true;
        }

        if ($type instanceof IterableType) {
            return true;
        }

        if ($type instanceof GenericObjectType) {
            if (! $this->reflectionProvider->hasClass($type->getClassName())) {
                return false;
            }

            $genericObjectType = $this->reflectionProvider->getClass($type->getClassName());
            if ($genericObjectType->implementsInterface('Traversable')) {
                return true;
            }
        }

        return false;
    }
}
