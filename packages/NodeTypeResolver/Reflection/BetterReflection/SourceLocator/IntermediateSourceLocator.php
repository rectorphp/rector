<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator;

use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use Rector\NodeTypeResolver\Contract\SourceLocatorProviderInterface;
final class IntermediateSourceLocator implements \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
{
    /**
     * @var \Rector\NodeTypeResolver\Contract\SourceLocatorProviderInterface[]
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
    /**
     * @param \PHPStan\BetterReflection\Reflector\Reflector $reflector
     * @param \PHPStan\BetterReflection\Identifier\Identifier $identifier
     */
    public function locateIdentifier($reflector, $identifier) : ?\PHPStan\BetterReflection\Reflection\Reflection
    {
        foreach ($this->sourceLocatorProviders as $sourceLocatorProvider) {
            $sourceLocator = $sourceLocatorProvider->provide();
            $reflection = $sourceLocator->locateIdentifier($reflector, $identifier);
            if ($reflection instanceof \PHPStan\BetterReflection\Reflection\Reflection) {
                return $reflection;
            }
        }
        return null;
    }
    /**
     * Find all identifiers of a type
     * @return Reflection[]
     * @param \PHPStan\BetterReflection\Reflector\Reflector $reflector
     * @param \PHPStan\BetterReflection\Identifier\IdentifierType $identifierType
     */
    public function locateIdentifiersByType($reflector, $identifierType) : array
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
