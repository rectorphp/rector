<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Printer;

use Nette\Utils\Strings;
use PhpParser\Lexer;
use Rector\Autodiscovery\ValueObject\NodesWithFileDestination;
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
        PostFileProcessor $postFileProcessor,
        Lexer $lexer,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->postFileProcessor = $postFileProcessor;
        $this->lexer = $lexer;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function printNodesWithFileDestination(NodesWithFileDestination $nodesWithFileDestination): string
    {
        $nodes = $this->postFileProcessor->traverse($nodesWithFileDestination->getNodes());

        // re-index keys from 0
        $nodes = array_values($nodes);

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
        if ($lastToken && Strings::contains($lastToken[1], "\n")) {
            $prettyPrintContent = trim($prettyPrintContent) . PHP_EOL;
        }

        return $prettyPrintContent;
    }
}
