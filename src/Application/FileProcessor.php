<?php declare(strict_types=1);

namespace Rector\Application;

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

    public function __construct(
        FormatPerservingPrinter $formatPerservingPrinter,
        NodeTraverserQueue $nodeTraverserQueue,
        RectorNodeTraverser $rectorNodeTraverser
    ) {
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->nodeTraverserQueue = $nodeTraverserQueue;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
    }

    /**
     * @param string[] $rectorClasses
     */
    public function processFileWithRectorsToString(SplFileInfo $file, array $rectorClasses): string
    {
        $this->rectorNodeTraverser->enableOnlyRectorClasses($rectorClasses);

        return $this->processFileToString($file);
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
