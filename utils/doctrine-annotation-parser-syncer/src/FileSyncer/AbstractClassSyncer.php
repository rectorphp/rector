<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\FileSyncer;

use PhpParser\Node;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\Utils\DoctrineAnnotationParserSyncer\ClassSyncerNodeTraverser;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\ClassSyncerInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

abstract class AbstractClassSyncer implements ClassSyncerInterface
{
    /**
     * @var SmartFileSystem
     */
    protected $smartFileSystem;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var FileInfoParser
     */
    private $fileInfoParser;

    /**
     * @var ClassSyncerNodeTraverser
     */
    private $classSyncerNodeTraverser;

    /**
     * @required
     */
    public function autowireAbstractClassSyncer(
        BetterStandardPrinter $betterStandardPrinter,
        FileInfoParser $fileInfoParser,
        SmartFileSystem $smartFileSystem,
        ClassSyncerNodeTraverser $classSyncerNodeTraverser
    ): void {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->fileInfoParser = $fileInfoParser;
        $this->smartFileSystem = $smartFileSystem;
        $this->classSyncerNodeTraverser = $classSyncerNodeTraverser;
    }

    public function sync(bool $isDryRun): bool
    {
        $fileNodes = $this->getFileNodes();
        $changedNodes = $this->classSyncerNodeTraverser->traverse($fileNodes);

        if ($isDryRun) {
            return ! $this->hasContentChanged($fileNodes);
        }

        $this->printNodesToPath($changedNodes);
        return true;
    }

    /**
     * @return Node[]
     */
    protected function getFileNodes(): array
    {
        $docParserFileInfo = new SmartFileInfo($this->getSourceFilePath());

        return $this->fileInfoParser->parseFileInfoToNodesAndDecorate($docParserFileInfo);
    }

    /**
     * @param Node[] $nodes
     */
    protected function printNodesToPath(array $nodes): void
    {
        $printedContent = $this->betterStandardPrinter->prettyPrintFile($nodes);

        $this->smartFileSystem->dumpFile($this->getTargetFilePath(), $printedContent);
    }

    /**
     * @param Node[] $nodes
     */
    protected function hasContentChanged(array $nodes): bool
    {
        $finalContent = $this->betterStandardPrinter->prettyPrintFile($nodes);

        // nothing to validate against
        if (! file_exists($this->getTargetFilePath())) {
            return false;
        }

        $currentContent = $this->smartFileSystem->readFile($this->getTargetFilePath());

        // has content changed
        return $finalContent !== $currentContent;
    }
}
