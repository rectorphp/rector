<?php declare(strict_types=1);

namespace Rector\Testing\Application;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use Rector\NodeTraverser\StateHolder;
use Rector\Printer\FormatPerservingPrinter;
use SplFileInfo;

final class FileReconstructor
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var FormatPerservingPrinter
     */
    private $codeStyledPrinter;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    /**
     * @var StateHolder
     */
    private $stateHolder;

    public function __construct(
        Parser $parser,
        FormatPerservingPrinter $codeStyledPrinter,
        Lexer $lexer,
        NodeTraverser $nodeTraverser,
        StateHolder $stateHolder
    ) {
        $this->parser = $parser;
        $this->codeStyledPrinter = $codeStyledPrinter;
        $this->lexer = $lexer;
        $this->nodeTraverser = $nodeTraverser;
        $this->stateHolder = $stateHolder;
    }

    // ref: https://github.com/nikic/PHP-Parser/issues/344#issuecomment-298162516
    public function processFile(SplFileInfo $file): string
    {
        $fileContent = file_get_contents($file->getRealPath());

        $oldStmts = $this->parser->parse($fileContent);
        $oldTokens = $this->lexer->getTokens();
        $newStmts = $this->nodeTraverser->traverse($oldStmts);

        if (! $this->stateHolder->isAfterTraverseCalled()) {
            [$newStmts, $oldStmts] = [$oldStmts, $newStmts];
        }

        return $this->codeStyledPrinter->printToString($newStmts, $oldStmts, $oldTokens);
    }
}
