<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Printer;

use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\Declare_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Namespace_;
use RectorPrefix20220606\Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use RectorPrefix20220606\Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
use RectorPrefix20220606\Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @todo re-use in https://github.com/rectorphp/rector-src/blob/main/rules/PSR4/Rector/Namespace_/MultipleClassFileToPsr4ClassesRector.php
 *
 * Printer useful for printing classes next to just-processed one.
 * E.g. in case of extracting class to the same directory, just with different name.
 */
final class NeighbourClassLikePrinter
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    public function __construct(BetterNodeFinder $betterNodeFinder, BetterStandardPrinter $betterStandardPrinter, RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    /**
     * @param \PhpParser\Node\Stmt\Namespace_|\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace $mainNode
     */
    public function printClassLike(ClassLike $classLike, $mainNode, SmartFileInfo $smartFileInfo, ?File $file = null) : void
    {
        $declares = $this->resolveDeclares($mainNode);
        if ($mainNode instanceof FileWithoutNamespace) {
            $nodesToPrint = \array_merge($declares, [$classLike]);
        } else {
            // use new class in the namespace
            $mainNode->stmts = [$classLike];
            $nodesToPrint = \array_merge($declares, [$mainNode]);
        }
        $fileDestination = $this->createClassLikeFileDestination($classLike, $smartFileInfo);
        $printedFileContent = $this->betterStandardPrinter->prettyPrintFile($nodesToPrint);
        $addedFileWithContent = new AddedFileWithContent($fileDestination, $printedFileContent);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);
    }
    private function createClassLikeFileDestination(ClassLike $classLike, SmartFileInfo $smartFileInfo) : string
    {
        $currentDirectory = \dirname($smartFileInfo->getRealPath());
        return $currentDirectory . \DIRECTORY_SEPARATOR . $classLike->name . '.php';
    }
    /**
     * @return Declare_[]
     * @param \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace|\PhpParser\Node\Stmt\Namespace_ $mainNode
     */
    private function resolveDeclares($mainNode) : array
    {
        $declare = $this->betterNodeFinder->findFirstPreviousOfTypes($mainNode, [Declare_::class]);
        if ($declare instanceof Declare_) {
            return [$declare];
        }
        return [];
    }
}
