<?php declare(strict_types=1);

namespace Rector\Application;

use Rector\FileSystem\CurrentFileProvider;
use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\NodeTraverserQueue\NodeTraverserQueue;
use Rector\Printer\FormatPerservingPrinter;
use SplFileInfo;

final class FileProcessor
{
    /**
     * @var FormatPerservingPrinter
     */
    private $formatPerservingPrinter;

    /**
     * @var NodeTraverserQueue
     */
    private $nodeTraverserQueue;

    /**
     * @var RectorNodeTraverser
     */
    private $rectorNodeTraverser;

    /**
     * @var CurrentFileProvider
     */
    private $currentFileProvider;

    public function __construct(
        FormatPerservingPrinter $codeStyledPrinter,
        NodeTraverserQueue $nodeTraverserQueue,
        RectorNodeTraverser $rectorNodeTraverser,
        CurrentFileProvider $currentFileProvider
    ) {
        $this->formatPerservingPrinter = $codeStyledPrinter;
        $this->nodeTraverserQueue = $nodeTraverserQueue;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->currentFileProvider = $currentFileProvider;
    }

    /**
     * @param string[] $rectorClasses
     */
    public function processFileWithRectorsToString(SplFileInfo $file, array $rectorClasses): string
    {
        $this->rectorNodeTraverser->enableOnlyRectorClasses($rectorClasses);

        return $this->processFileToString($file);
    }

    public function processFile(SplFileInfo $fileInfo): void
    {
        $this->currentFileProvider->setCurrentFile($fileInfo);

        [$newStmts, $oldStmts, $oldTokens] = $this->nodeTraverserQueue->processFileInfo($fileInfo);

        $this->formatPerservingPrinter->printToFile($fileInfo, $newStmts, $oldStmts, $oldTokens);
    }

    /**
     * See https://github.com/nikic/PHP-Parser/issues/344#issuecomment-298162516.
     */
    private function processFileToString(SplFileInfo $fileInfo): string
    {
        $this->currentFileProvider->setCurrentFile($fileInfo);

        [$newStmts, $oldStmts, $oldTokens] = $this->nodeTraverserQueue->processFileInfo($fileInfo);

        return $this->formatPerservingPrinter->printToString($newStmts, $oldStmts, $oldTokens);
    }
}
