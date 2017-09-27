<?php declare(strict_types=1);

namespace Rector\Application;

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

    public function __construct(FormatPerservingPrinter $codeStyledPrinter, NodeTraverserQueue $nodeTraverserQueue)
    {
        $this->formatPerservingPrinter = $codeStyledPrinter;
        $this->nodeTraverserQueue = $nodeTraverserQueue;
    }

    /**
     * @param SplFileInfo[] $files
     */
    public function processFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->processFile($file);
        }
    }

    /**
     * @todo refactor to common NodeTraverserQueue [$file => $newStatements, $oldStatements, $oldTokens]
     * Apply in testing files processors as well
     */
    public function processFile(SplFileInfo $fileInfo): void
    {
        [$newStmts, $oldStmts, $oldTokens] = $this->nodeTraverserQueue->processFileInfo($fileInfo);

        $this->formatPerservingPrinter->printToFile($fileInfo, $newStmts, $oldStmts, $oldTokens);
    }
}
