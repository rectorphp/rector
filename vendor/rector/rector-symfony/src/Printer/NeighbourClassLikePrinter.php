<?php

declare (strict_types=1);
namespace Rector\Symfony\Printer;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\ValueObject\Application\File;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
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
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    /**
     * @param \PhpParser\Node\Stmt\Namespace_|\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace $mainNode
     */
    public function printClassLike(\PhpParser\Node\Stmt\ClassLike $classLike, $mainNode, \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, ?\Rector\Core\ValueObject\Application\File $file = null) : void
    {
        $declares = $this->resolveDeclares($mainNode, $file);
        if ($mainNode instanceof \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace) {
            $nodesToPrint = \array_merge($declares, [$classLike]);
        } else {
            // use new class in the namespace
            $mainNode->stmts = [$classLike];
            $nodesToPrint = \array_merge($declares, [$mainNode]);
        }
        $fileDestination = $this->createClassLikeFileDestination($classLike, $smartFileInfo);
        $printedFileContent = $this->betterStandardPrinter->prettyPrintFile($nodesToPrint);
        $addedFileWithContent = new \Rector\FileSystemRector\ValueObject\AddedFileWithContent($fileDestination, $printedFileContent);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);
    }
    private function createClassLikeFileDestination(\PhpParser\Node\Stmt\ClassLike $classLike, \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : string
    {
        $currentDirectory = \dirname($smartFileInfo->getRealPath());
        return $currentDirectory . \DIRECTORY_SEPARATOR . $classLike->name . '.php';
    }
    /**
     * @return Declare_[]
     * @param \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace|\PhpParser\Node\Stmt\Namespace_ $mainNode
     */
    private function resolveDeclares($mainNode, ?\Rector\Core\ValueObject\Application\File $file = null) : array
    {
        $declare = $this->betterNodeFinder->findFirstPreviousOfTypes($mainNode, [\PhpParser\Node\Stmt\Declare_::class], $file);
        if ($declare instanceof \PhpParser\Node\Stmt\Declare_) {
            return [$declare];
        }
        return [];
    }
}
