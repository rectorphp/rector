<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Printer;

use Nette\Utils\Strings;
use PhpParser\Lexer;
use Rector\FileSystemRector\Contract\FileWithNodesInterface;
use Rector\PostRector\Application\PostFileProcessor;

final class NodesWithFileDestinationPrinter
{
    public function __construct(
        private BetterStandardPrinter $betterStandardPrinter,
        private Lexer $lexer,
        private PostFileProcessor $postFileProcessor
    ) {
    }

    public function printNodesWithFileDestination(FileWithNodesInterface $fileWithNodes): string
    {
        $nodes = $this->postFileProcessor->traverse($fileWithNodes->getNodes());
        $prettyPrintContent = $this->betterStandardPrinter->prettyPrintFile($nodes);

        return $this->resolveLastEmptyLine($prettyPrintContent);
    }

    /**
     * Add empty line in the end, if it is in the original tokens
     */
    private function resolveLastEmptyLine(string $prettyPrintContent): string
    {
        $tokens = $this->lexer->getTokens();
        $lastToken = array_pop($tokens);

        if ($lastToken && isset($lastToken[1]) && Strings::contains($lastToken[1], "\n")) {
            $prettyPrintContent = trim($prettyPrintContent) . PHP_EOL;
        }

        return $prettyPrintContent;
    }
}
