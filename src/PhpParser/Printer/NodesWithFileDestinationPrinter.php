<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Printer;

use Nette\Utils\Strings;
use PhpParser\Lexer;
use PhpParser\ParserFactory;
use Rector\Autodiscovery\ValueObject\NodesWithFileDestination;
use Rector\Core\Application\TokensByFilePathStorage;
use Rector\PostRector\Application\PostFileProcessor;
use TypeError;

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
     * @var ParserFactory
     */
    private $parserFactory;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var FormatPerservingPrinter
     */
    private $formatPerservingPrinter;

    /**
     * @var TokensByFilePathStorage
     */
    private $tokensByFilePathStorage;

    public function __construct(
        PostFileProcessor $postFileProcessor,
        ParserFactory $parserFactory,
        Lexer $lexer,
        BetterStandardPrinter $betterStandardPrinter,
        FormatPerservingPrinter $formatPerservingPrinter,
        TokensByFilePathStorage $tokensByFilePathStorage
    ) {
        $this->postFileProcessor = $postFileProcessor;
        $this->parserFactory = $parserFactory;
        $this->lexer = $lexer;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->tokensByFilePathStorage = $tokensByFilePathStorage;
    }

    public function printNodesWithFileDestination(NodesWithFileDestination $nodesWithFileDestination): string
    {
        $nodes = $this->postFileProcessor->traverse($nodesWithFileDestination->getNodes());

        // re-index keys from 0
        $nodes = array_values($nodes);

        $parsedStmtsAndTokens = $this->tokensByFilePathStorage->getForFileInfo(
            $nodesWithFileDestination->getOriginalSmartFileInfo()
        );

        // 1. if nodes are the same, prefer format preserving printer
        try {
            $dummyLexer = new Lexer();
            $dummyParser = $this->parserFactory->create(ParserFactory::PREFER_PHP7, $dummyLexer);
            $dummyParser->parse('<?php ' . $this->betterStandardPrinter->print($nodes));

            $dummyTokenCount = count($dummyLexer->getTokens());
            $modelTokenCount = count($parsedStmtsAndTokens->getOldTokens());

            if ($dummyTokenCount > $modelTokenCount) {
                // nothing we can do - this would end by "Undefined offset in TokenStream.php on line X" error
                $formatPreservingContent = '';
            } else {
                $formatPreservingContent = $this->formatPerservingPrinter->printToString(
                    $nodes,
                    $parsedStmtsAndTokens->getOldStmts(),
                    $this->lexer->getTokens()
                );
            }
        } catch (TypeError $typeError) {
            // incompatible tokens, nothing we can do to preserve format
            $formatPreservingContent = '';
        }

        $prettyPrintContent = $this->betterStandardPrinter->prettyPrintFile($nodes);

        if ($this->areStringsSameWithoutSpaces($formatPreservingContent, $prettyPrintContent)) {
            return $formatPreservingContent;
        }

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

    /**
     * Also without FQN "\" that are added by basic printer
     */
    private function areStringsSameWithoutSpaces(string $firstString, string $secondString): bool
    {
        return $this->clearString($firstString) === $this->clearString($secondString);
    }

    private function clearString(string $string): string
    {
        $string = $this->removeComments($string);

        // remove all spaces
        $string = Strings::replace($string, '#\s+#', '');

        // remove FQN "\" that are added by basic printer
        $string = Strings::replace($string, '#\\\\#', '');

        // remove trailing commas, as one of them doesn't have to contain them
        return Strings::replace($string, '#\,#', '');
    }

    /**
     * @todo extra to PrintedContentCleaner service
     */
    private function removeComments(string $string): string
    {
        // remove comments like this noe
        $string = Strings::replace($string, '#\/\/(.*?)\n#', '');

        $string = Strings::replace($string, '#/\*.*?\*/#s', '');

        return Strings::replace($string, '#\n\s*\n#', "\n");
    }
}
