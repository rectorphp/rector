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

    public function __construct(
        FormatPerservingPrinter $formatPerservingPrinter,
        NodeTraverserQueue $nodeTraverserQueue
    ) {
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->nodeTraverserQueue = $nodeTraverserQueue;
    }

    public function processFile(SplFileInfo $fileInfo): string
    {
        [$newStmts, $oldStmts, $oldTokens] = $this->nodeTraverserQueue->processFileInfo($fileInfo);

        return $this->formatPerservingPrinter->printToFile($fileInfo, $newStmts, $oldStmts, $oldTokens);
    }

    /**
     * See https://github.com/nikic/PHP-Parser/issues/344#issuecomment-298162516.
     */
    public function processFileToString(SplFileInfo $fileInfo): string
    {
        [$newStmts, $oldStmts, $oldTokens] = $this->nodeTraverserQueue->processFileInfo($fileInfo);

        return $this->formatPerservingPrinter->printToString($newStmts, $oldStmts, $oldTokens);
    }
}
