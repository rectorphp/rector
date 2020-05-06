<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\ReturnTypeExtension;

use PHPStan\Reflection\MethodReflection;
use Rector\Core\Rector\AbstractRector;

final class NameResolverTraitReturnTypeExtension extends AbstractResolvedNameReturnTypeExtension
{
    /**
     * Original scope @see NameResolverTrait
     */
    public function getClass(): string
    {
        return AbstractRector::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getName';
    }
}
