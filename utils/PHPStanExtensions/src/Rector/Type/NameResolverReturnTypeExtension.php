<?php declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rector\Type;

use PHPStan\Reflection\MethodReflection;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class NameResolverReturnTypeExtension extends AbstractResolvedNameReturnTypeExtension
{
    public function getClass(): string
    {
        return NameResolver::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'resolve';
    }
}
