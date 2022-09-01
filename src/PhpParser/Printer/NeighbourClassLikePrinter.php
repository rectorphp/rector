<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Printer;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\ValueObject\Application\File;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
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
    public function __construct(BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter, RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    /**
     * @param \PhpParser\Node\Stmt\Namespace_|\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace $mainNode
     */
    public function printClassLike(ClassLike $classLike, $mainNode, string $filePath, ?File $file = null) : void
    {
        $declares = $this->resolveDeclares($mainNode);
        if ($mainNode instanceof FileWithoutNamespace) {
            $nodesToPrint = \array_merge($declares, [$classLike]);
        } else {
            // use new class in the namespace
            $mainNode->stmts = [$classLike];
            $nodesToPrint = \array_merge($declares, [$mainNode]);
        }
        $fileDestination = $this->createClassLikeFileDestination($classLike, $filePath);
        $printedFileContent = $this->betterStandardPrinter->prettyPrintFile($nodesToPrint);
        $addedFileWithContent = new AddedFileWithContent($fileDestination, $printedFileContent);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);
    }
    private function createClassLikeFileDestination(ClassLike $classLike, string $filePath) : string
    {
        $currentDirectory = \dirname($filePath);
        return $currentDirectory . \DIRECTORY_SEPARATOR . $classLike->name . '.php';
    }
    /**
     * @return Declare_[]
     * @param \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace|\PhpParser\Node\Stmt\Namespace_ $mainNode
     */
    private function resolveDeclares($mainNode) : array
    {
        $node = $this->betterNodeFinder->findFirstPreviousOfTypes($mainNode, [Declare_::class]);
        if ($node instanceof Declare_) {
            return [$node];
        }
        return [];
    }
}
