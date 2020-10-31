<?php

declare(strict_types=1);

namespace Rector\NewFilePrinter\Printer;

use Nette\Utils\Strings;
use PhpParser\Lexer;
use PhpParser\Node;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\PostRector\Application\PostFileProcessor;

final class NodesToDestinationPrinter
{
    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var PostFileProcessor
     */
    private $postFileProcessor;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;

    public function __construct(
        PostFileProcessor $postFileProcessor,
        BetterStandardPrinter $betterStandardPrinter,
        Lexer $lexer,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector
    ) {
        $this->postFileProcessor = $postFileProcessor;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->lexer = $lexer;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }

    /**
     * @param Node[]|Node $nodes
     */
    public function printNewNodesToFilePath($nodes, string $fileDestination): void
    {
        if (! is_array($nodes)) {
            $nodes = [$nodes];
        }

        $nodes = $this->postFileProcessor->traverse($nodes);

        $fileContent = $this->betterStandardPrinter->prettyPrintFile($nodes);
        $fileContent = $this->resolveLastEmptyLine($fileContent);

        $this->removedAndAddedFilesCollector->addFileWithContent($fileDestination, $fileContent);
    }

    /**
     * Add empty line in the end, if it is in the original tokens
     */
    private function resolveLastEmptyLine(string $prettyPrintContent): string
    {
        $tokens = $this->lexer->getTokens();
        $lastToken = array_pop($tokens);
        if ($lastToken && Strings::contains($lastToken[1], "\n")) {
            $prettyPrintContent = trim($prettyPrintContent) . PHP_EOL;
        }

        return $prettyPrintContent;
    }
}
