<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use PhpParser\Node;
use Rector\CodingStyle\ClassNameImport\ShortNameResolver;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

/**
 * Prevents adding:
 *
 * use App\SomeClass;
 *
 * If there is already:
 *
 * SomeClass::callThis();
 */
final class FullyQualifiedNameClassNameImportSkipVoter implements ClassNameImportSkipVoterInterface
{
    public function __construct(
        private ShortNameResolver $shortNameResolver,
        private CurrentFileProvider $currentFileProvider
    ) {
    }

    public function shouldSkip(FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node): bool
    {
        // "new X" or "X::static()"
        $file = $this->currentFileProvider->getFile();
        $shortNamesToFullyQualifiedNames = $this->shortNameResolver->resolveForNode($file);

        foreach ($shortNamesToFullyQualifiedNames as $shortName => $fullyQualifiedName) {
            $shortNameLowered = strtolower($shortName);
            if ($fullyQualifiedObjectType->getShortNameLowered() !== $shortNameLowered) {
                continue;
            }

            return $fullyQualifiedObjectType->getClassNameLowered() !== strtolower($fullyQualifiedName);
        }

        return false;
    }
}
