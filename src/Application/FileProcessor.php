<?php declare(strict_types=1);

namespace Rector\Application;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use Rector\Contract\Parser\ParserInterface;
use Rector\NodeTraverser\CloningNodeTraverser;
use Rector\NodeTraverser\MainNodeTraverser;
use Rector\NodeTraverser\ShutdownNodeTraverser;
use Rector\NodeTraverser\StandaloneTraverseNodeTraverser;
use Rector\Printer\FormatPerservingPrinter;
use SplFileInfo;

final class FileProcessor
{
    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var FormatPerservingPrinter
     */
    private $formatPerservingPrinter;

    /**
     * @var NodeTraverser
     */
    private $mainNodeTraverser;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var CloningNodeTraverser
     */
    private $cloningNodeTraverser;

    /**
     * @var ShutdownNodeTraverser
     */
    private $shutdownNodeTraverser;

    /**
     * @var StandaloneTraverseNodeTraverser
     */
    private $standaloneTraverseNodeTraverser;

    public function __construct(
        ParserInterface $parser,
        FormatPerservingPrinter $codeStyledPrinter,
        Lexer $lexer,
        MainNodeTraverser $mainNodeTraverser,
        CloningNodeTraverser $cloningNodeTraverser,
        ShutdownNodeTraverser $shutdownNodeTraverser,
        StandaloneTraverseNodeTraverser $standaloneTraverseNodeTraverser
    ) {
        $this->parser = $parser;
        $this->formatPerservingPrinter = $codeStyledPrinter;
        $this->mainNodeTraverser = $mainNodeTraverser;
        $this->lexer = $lexer;
        $this->cloningNodeTraverser = $cloningNodeTraverser;
        $this->shutdownNodeTraverser = $shutdownNodeTraverser;
        $this->standaloneTraverseNodeTraverser = $standaloneTraverseNodeTraverser;
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
    public function processFile(SplFileInfo $file): void
    {
        $oldStmts = $this->parser->parseFile($file->getRealPath());

        $oldTokens = $this->lexer->getTokens();
        $newStmts = $this->cloningNodeTraverser->traverse($oldStmts);

        $newStmts = $this->standaloneTraverseNodeTraverser->traverse($newStmts);

        $newStmts = $this->mainNodeTraverser->traverse($newStmts);
        $newStmts = $this->shutdownNodeTraverser->traverse($newStmts);

        $this->formatPerservingPrinter->printToFile($file, $newStmts, $oldStmts, $oldTokens);
    }
}
