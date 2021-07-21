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
 * class SomeClass {}
 */
final class ClassLikeNameClassNameImportSkipVoter implements ClassNameImportSkipVoterInterface
{
    public function __construct(
        private ShortNameResolver $shortNameResolver
    ) {
    }

    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node): bool
    {
        $classLikeNames = $this->shortNameResolver->resolveShortClassLikeNamesForNode($node);

        foreach ($classLikeNames as $classLikeName) {
            if (strtolower($classLikeName) === $fullyQualifiedObjectType->getShortNameLowered()) {
                return true;
            }
        }

        return false;
    }
}
