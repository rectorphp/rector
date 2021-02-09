<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Printer;

use Nette\Utils\Strings;
use PhpParser\Lexer;
use Rector\FileSystemRector\Contract\FileWithNodesInterface;
use Rector\PostRector\Application\PostFileProcessor;

final class NodesWithFileDestinationPrinter
{
    /**
     * @var PostFileProcessor
     */
    private $postFileProcessor;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        Lexer $lexer,
        PostFileProcessor $postFileProcessor
    ) {
        $this->postFileProcessor = $postFileProcessor;
        $this->lexer = $lexer;
        $this->betterStandardPrinter = $betterStandardPrinter;
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
