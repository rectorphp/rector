<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\ReturnTypeExtension;

use PhpParser\NodeVisitor\NameResolver;
use PHPStan\Reflection\MethodReflection;

final class NameResolverReturnTypeExtension extends AbstractResolvedNameReturnTypeExtension
{
    public function getClass(): string
    {
        return NameResolver::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getName';
    }
}
