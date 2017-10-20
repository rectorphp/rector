<?php declare(strict_types=1);

namespace Rector\BetterReflection\SourceLocator;

use Rector\BetterReflection\Identifier\Identifier;
use Rector\BetterReflection\Identifier\IdentifierType;
use Rector\BetterReflection\Reflection\Reflection;
use Rector\BetterReflection\Reflector\Exception\IdentifierNotFound;
use Rector\BetterReflection\Reflector\Reflector;
use Rector\BetterReflection\SourceLocator\Ast\Locator;
use Rector\BetterReflection\SourceLocator\Located\LocatedSource;
use Rector\BetterReflection\SourceLocator\Type\SourceLocator;
use Rector\BetterReflection\Stubber\SourceStubber;

/**
 * Located class in local stubs, as fallback locator if any other fails.
 * This allows to perform source code analyses without dependency on /vendor and analysed source code.
 */
final class StubSourceLocator implements SourceLocator
{
    /**
     * @var SourceStubber
     */
    private $sourceStubber;

    /**
     * @var Locator
     */
    private $astLocator;

    public function __construct(Locator $locator, SourceStubber $sourceStubber)
    {
        $this->sourceStubber = $sourceStubber;
        $this->astLocator = $locator;
    }

    public function locateIdentifier(Reflector $reflector, Identifier $identifier): ?Reflection
    {
        if (! $identifier->isClass()) {
            return null;
        }

        $stubFileInfo = $this->sourceStubber->getStubFileInfoForName($identifier->getName());
        if ($stubFileInfo === null) {
            return null;
        }

        $locatedSource = new LocatedSource($stubFileInfo->getContents(), $stubFileInfo->getRealPath());

        try {
            return $this->astLocator->findReflection($reflector, $locatedSource, $identifier);
        } catch (IdentifierNotFound $identifierNotFoundException) {
            return null;
        }
    }

    /**
     * @return mixed[]
     */
    public function locateIdentifiersByType(Reflector $reflector, IdentifierType $identifierType): array
    {
        return [];
    }
}
