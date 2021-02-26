<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator;

use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
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

        $reflection = $sourceLocator->locateIdentifier($reflector, $identifier);
        if ($reflection instanceof Reflection) {
            return $reflection;
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
