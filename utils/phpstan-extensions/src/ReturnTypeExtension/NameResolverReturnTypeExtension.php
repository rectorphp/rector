<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\ReturnTypeExtension;

use PHPStan\Reflection\MethodReflection;
use Rector\NodeNameResolver\NodeNameResolver;

final class NameResolverReturnTypeExtension extends AbstractResolvedNameReturnTypeExtension
{
    public function getClass(): string
    {
        return NodeNameResolver::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getName';
    }
}
