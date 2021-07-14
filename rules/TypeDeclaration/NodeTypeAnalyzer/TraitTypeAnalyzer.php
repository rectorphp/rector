<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeTypeAnalyzer;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class TraitTypeAnalyzer
{
    public function __construct(
        private NodeTypeResolver $nodeTypeResolver,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function isTraitType(Type $type): bool
    {
        if (! $type instanceof TypeWithClassName) {
            return false;
        }

        $fullyQualifiedName = $this->nodeTypeResolver->getFullyQualifiedClassName($type);

        if (! $this->reflectionProvider->hasClass($fullyQualifiedName)) {
            return false;
        }

        $classReflection = $this->reflectionProvider->getClass($fullyQualifiedName);
        return $classReflection->isTrait();
    }
}
