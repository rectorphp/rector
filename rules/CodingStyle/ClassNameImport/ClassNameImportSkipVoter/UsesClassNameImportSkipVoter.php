<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use PhpParser\Node;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\ValueObject\Application\File;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
/**
 * This prevents importing:
 * - App\Some\Product
 *
 * if there is already:
 * - use App\Another\Product
 */
final class UsesClassNameImportSkipVoter implements \Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface
{
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\UseNodesToAddCollector
     */
    private $useNodesToAddCollector;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    public function __construct(\Rector\PostRector\Collector\UseNodesToAddCollector $useNodesToAddCollector, \Rector\Core\Configuration\RenamedClassesDataCollector $renamedClassesDataCollector)
    {
        $this->useNodesToAddCollector = $useNodesToAddCollector;
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
    }
    public function shouldSkip(\Rector\Core\ValueObject\Application\File $file, \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType, \PhpParser\Node $node) : bool
    {
        $useImportTypes = $this->useNodesToAddCollector->getUseImportTypesByNode($file, $node);
        foreach ($useImportTypes as $useImportType) {
            // if the class is renamed, the use import is no longer blocker
            if ($this->renamedClassesDataCollector->hasOldClass($useImportType->getClassName())) {
                continue;
            }
            if (!$useImportType->equals($fullyQualifiedObjectType) && $useImportType->areShortNamesEqual($fullyQualifiedObjectType)) {
                return \true;
            }
            if ($useImportType->equals($fullyQualifiedObjectType)) {
                return \false;
            }
        }
        return \false;
    }
}
