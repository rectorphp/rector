<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator;

use _HumbugBoxfac515c46e83\Roave\BetterReflection\Identifier\Identifier;
use _HumbugBoxfac515c46e83\Roave\BetterReflection\Identifier\IdentifierType;
use _HumbugBoxfac515c46e83\Roave\BetterReflection\Reflection\Reflection;
use _HumbugBoxfac515c46e83\Roave\BetterReflection\Reflector\Reflector;
use _HumbugBoxfac515c46e83\Roave\BetterReflection\SourceLocator\Type\SourceLocator;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocator;

final class IntermediateSourceLocator implements SourceLocator
{
    /**
     * @var DynamicSourceLocator
     */
    private $dynamicSourceLocator;

    public function __construct(DynamicSourceLocator $dynamicSourceLocator)
    {
        $this->dynamicSourceLocator = $dynamicSourceLocator;
    }

    public function locateIdentifier(Reflector $reflector, Identifier $identifier): ?Reflection
    {
        $sourceLocator = $this->dynamicSourceLocator->provide();

        $locatedIdentifier = $sourceLocator->locateIdentifier($reflector, $identifier);
        if ($locatedIdentifier instanceof Reflection) {
            return $locatedIdentifier;
        }

        return null;
    }

    /**
     * Find all identifiers of a type
     * @return Reflection[]
     */
    public function locateIdentifiersByType(Reflector $reflector, IdentifierType $identifierType): array
    {
        $sourceLocator = $this->dynamicSourceLocator->provide();

        $reflections = $sourceLocator->locateIdentifiersByType($reflector, $identifierType);
        if ($reflections !== []) {
            return $reflections;
        }

        return [];
    }
}
