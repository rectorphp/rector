<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use PhpParser\Node;
use Rector\CodingStyle\ClassNameImport\ShortNameResolver;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\Core\ValueObject\Application\File;
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
    ) {
    }

    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node): bool
    {
        // "new X" or "X::static()"
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
