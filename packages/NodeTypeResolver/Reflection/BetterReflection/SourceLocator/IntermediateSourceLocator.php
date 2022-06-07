<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator;

use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use Rector\NodeTypeResolver\Contract\SourceLocatorProviderInterface;
final class IntermediateSourceLocator implements SourceLocator
{
    /**
     * @var SourceLocatorProviderInterface[]
     * @readonly
     */
    private $sourceLocatorProviders;
    /**
     * @param SourceLocatorProviderInterface[] $sourceLocatorProviders
     */
    public function __construct(array $sourceLocatorProviders)
    {
        $this->sourceLocatorProviders = $sourceLocatorProviders;
    }
    public function locateIdentifier(Reflector $reflector, Identifier $identifier) : ?Reflection
    {
        foreach ($this->sourceLocatorProviders as $sourceLocatorProvider) {
            $sourceLocator = $sourceLocatorProvider->provide();
            $reflection = $sourceLocator->locateIdentifier($reflector, $identifier);
            if ($reflection instanceof Reflection) {
                return $reflection;
            }
        }
        return null;
    }
    /**
     * Find all identifiers of a type
     * @return array<int, Reflection>
     */
    public function locateIdentifiersByType(Reflector $reflector, IdentifierType $identifierType) : array
    {
        foreach ($this->sourceLocatorProviders as $sourceLocatorProvider) {
            $sourceLocator = $sourceLocatorProvider->provide();
            $reflections = $sourceLocator->locateIdentifiersByType($reflector, $identifierType);
            if ($reflections !== []) {
                return $reflections;
            }
        }
        return [];
    }
}
