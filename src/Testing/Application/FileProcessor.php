<?php declare(strict_types=1);

namespace Rector\Testing\Application;

use PhpParser\NodeVisitor;
use Rector\NodeTraverser\MainNodeTraverser;
use Rector\NodeTraverserQueue\NodeTraverserQueue;
use Rector\Printer\FormatPerservingPrinter;
use Rector\Rector\RectorCollector;
use SplFileInfo;

final class FileProcessor
{
    /**
     * @var FormatPerservingPrinter
     */
    private $codeStyledPrinter;

    /**
     * @var MainNodeTraverser
     */
    private $mainNodeTraverser;

    /**
     * @var RectorCollector
     */
    private $rectorCollector;

    /**
     * @var NodeTraverserQueue
     */
    private $nodeTraverserQueue;

    public function __construct(
        FormatPerservingPrinter $codeStyledPrinter,
        MainNodeTraverser $mainNodeTraverser,
        RectorCollector $rectorCollector,
        NodeTraverserQueue $nodeTraverserQueue
    ) {
        $this->codeStyledPrinter = $codeStyledPrinter;
        $this->mainNodeTraverser = $mainNodeTraverser;
        $this->rectorCollector = $rectorCollector;
        $this->nodeTraverserQueue = $nodeTraverserQueue;
    }

    /**
     * @param string[] $rectorClasses
     */
    public function processFileWithRectors(SplFileInfo $file, array $rectorClasses): string
    {
        foreach ($rectorClasses as $rectorClass) {
            /** @var NodeVisitor $rector */
            $rector = $this->rectorCollector->getRector($rectorClass);
            $this->mainNodeTraverser->addVisitor($rector);
        }

        $content = $this->processFile($file);

        foreach ($rectorClasses as $rectorClass) {
            /** @var NodeVisitor $rector */
            $rector = $this->rectorCollector->getRector($rectorClass);
            $this->mainNodeTraverser->removeVisitor($rector);
        }

        return $content;
    }

    /**
     * See https://github.com/nikic/PHP-Parser/issues/344#issuecomment-298162516.
     */
    public function processFile(SplFileInfo $fileInfo): string
    {
        [$newStmts, $oldStmts, $oldTokens] = $this->nodeTraverserQueue->processFileInfo($fileInfo);

        return $this->codeStyledPrinter->printToString($newStmts, $oldStmts, $oldTokens);
    }
}
