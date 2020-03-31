<?php

declare(strict_types=1);

namespace Rector\PostRector\Validator;

use PhpParser\Node;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PostRector\Collector\UseNodesToAddCollector;

final class CanImportBeAddedValidator
{
    /**
     * @var UseNodesToAddCollector
     */
    private $useNodesToAddCollector;

    public function __construct(UseNodesToAddCollector $useNodesToAddCollector)
    {
        $this->useNodesToAddCollector = $useNodesToAddCollector;
    }

    /**
     * This prevents importing:
     * - App\Some\Product
     *
     * if there is already:
     * - use App\Another\Product
     */
    public function canImportBeAdded(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $useImportTypes = $this->useNodesToAddCollector->getUseImportTypesByNode($node);

        foreach ($useImportTypes as $useImportType) {
            if (! $useImportType->equals($fullyQualifiedObjectType) &&
                $useImportType->areShortNamesEqual($fullyQualifiedObjectType)
            ) {
                return false;
            }

            if ($useImportType->equals($fullyQualifiedObjectType)) {
                return true;
            }
        }

        return true;
    }
}
