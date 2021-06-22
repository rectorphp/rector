<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use PhpParser\Node;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

/**
 * This prevents importing:
 * - App\Some\Product
 *
 * if there is already:
 * - use App\Another\Product
 */
final class UsesClassNameImportSkipVoter implements ClassNameImportSkipVoterInterface
{
    public function __construct(
        private UseNodesToAddCollector $useNodesToAddCollector,
        private RenamedClassesDataCollector $renamedClassesDataCollector
    ) {
    }

    public function shouldSkip(FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node): bool
    {
        $useImportTypes = $this->useNodesToAddCollector->getUseImportTypesByNode($node);

        foreach ($useImportTypes as $useImportType) {
            // if the class is renamed, the use import is no longer blocker
            if ($this->renamedClassesDataCollector->hasOldClass($useImportType->getClassName())) {
                continue;
            }

            if (! $useImportType->equals($fullyQualifiedObjectType) && $useImportType->areShortNamesEqual(
                $fullyQualifiedObjectType
            )) {
                return true;
            }

            if ($useImportType->equals($fullyQualifiedObjectType)) {
                return false;
            }
        }

        return false;
    }
}
