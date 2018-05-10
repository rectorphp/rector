<?php declare(strict_types=1);

namespace Rector\BetterReflection\SourceLocator;

use Rector\BetterReflection\Stubber\SourceStubber;
use Roave\BetterReflection\Identifier\Identifier;
use Roave\BetterReflection\Identifier\IdentifierType;
use Roave\BetterReflection\Reflection\Reflection;
use Roave\BetterReflection\Reflector\Exception\IdentifierNotFound;
use Roave\BetterReflection\Reflector\Reflector;
use Roave\BetterReflection\SourceLocator\Ast\Locator;
use Roave\BetterReflection\SourceLocator\Located\LocatedSource;
use Roave\BetterReflection\SourceLocator\Type\SourceLocator;

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
