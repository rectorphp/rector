<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\FileSyncer;

use PhpParser\Node;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\Utils\DoctrineAnnotationParserSyncer\ClassSyncerNodeTraverser;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class AnnotationReaderClassSyncer
{
    /**
     * @var array<string, string>
     */
    private const VENDOR_FILES_TO_LOCAL_FILES = [
        __DIR__ . '/../../../../vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationReader.php' => __DIR__ . '/../../../../packages/DoctrineAnnotationGenerated/ConstantPreservingAnnotationReader.php',
        __DIR__ . '/../../../../vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/DocParser.php' => __DIR__ . '/../../../../packages/DoctrineAnnotationGenerated/ConstantPreservingDocParser.php',
    ];

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var FileInfoParser
     */
    private $fileInfoParser;

    /**
     * @var ClassSyncerNodeTraverser
     */
    private $classSyncerNodeTraverser;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        FileInfoParser $fileInfoParser,
        SmartFileSystem $smartFileSystem,
        ClassSyncerNodeTraverser $classSyncerNodeTraverser
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->fileInfoParser = $fileInfoParser;
        $this->smartFileSystem = $smartFileSystem;
        $this->classSyncerNodeTraverser = $classSyncerNodeTraverser;
    }

    public function sync(bool $isDryRun): bool
    {
        foreach (self::VENDOR_FILES_TO_LOCAL_FILES as $vendorFile => $localFile) {
            $docParserFileInfo = new SmartFileInfo($vendorFile);
            $fileNodes = $this->fileInfoParser->parseFileInfoToNodesAndDecorate($docParserFileInfo);
            $changedNodes = $this->classSyncerNodeTraverser->traverse($fileNodes);

            if ($isDryRun) {
                return ! $this->hasContentChanged($fileNodes, $localFile);
            }

            // print file
            $printedContent = $this->betterStandardPrinter->prettyPrintFile($changedNodes);
            $this->smartFileSystem->dumpFile($localFile, $printedContent);

            return true;
        }
    }

    /**
     * @param Node[] $nodes
     */
    private function hasContentChanged(array $nodes, string $targetFilePath): bool
    {
        $finalContent = $this->betterStandardPrinter->prettyPrintFile($nodes);

        // nothing to validate against
        if (! file_exists($targetFilePath)) {
            return false;
        }

        $currentContent = $this->smartFileSystem->readFile($targetFilePath);

        // has content changed
        return $finalContent !== $currentContent;
    }
}
