<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\FileSyncer;

use Nette\Utils\FileSystem;
use PhpParser\Node;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\ClassSyncerInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractClassSyncer implements ClassSyncerInterface
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var FileInfoParser
     */
    private $fileInfoParser;

    /**
     * @required
     */
    public function autowireAbstractClassSyncer(
        BetterStandardPrinter $betterStandardPrinter,
        FileInfoParser $fileInfoParser
    ): void {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->fileInfoParser = $fileInfoParser;
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

        FileSystem::write($this->getTargetFilePath(), $printedContent);
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

        $currentContent = FileSystem::read($this->getTargetFilePath());

        // has content changed
        return $finalContent !== $currentContent;
    }
}
