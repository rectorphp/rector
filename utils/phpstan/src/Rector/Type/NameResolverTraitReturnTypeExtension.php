<?php declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rector\Type;

use PHPStan\Reflection\MethodReflection;
use Rector\Rector\AbstractRector;
use Rector\Rector\NameResolverTrait;

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
