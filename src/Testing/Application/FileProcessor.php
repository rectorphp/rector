<?php declare(strict_types=1);

namespace Rector\Testing\Application;

use PhpParser\Lexer;
use PhpParser\NodeVisitor;
use Rector\Contract\Parser\ParserInterface;
use Rector\NodeTraverser\CloningNodeTraverser;
use Rector\NodeTraverser\MainNodeTraverser;
use Rector\NodeTraverser\ShutdownNodeTraverser;
use Rector\NodeTraverser\StandaloneTraverseNodeTraverser;
use Rector\Printer\FormatPerservingPrinter;
use Rector\Rector\RectorCollector;
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
    private $codeStyledPrinter;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var MainNodeTraverser
     */
    private $mainNodeTraverser;

    /**
     * @var RectorCollector
     */
    private $rectorCollector;

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
        CloningNodeTraverser $cloningNodeTraverser,
        ParserInterface $parser,
        FormatPerservingPrinter $codeStyledPrinter,
        Lexer $lexer,
        MainNodeTraverser $mainNodeTraverser,
        RectorCollector $rectorCollector,
        ShutdownNodeTraverser $shutdownNodeTraverser,
        StandaloneTraverseNodeTraverser $standaloneTraverseNodeTraverser
    ) {
        $this->parser = $parser;
        $this->codeStyledPrinter = $codeStyledPrinter;
        $this->lexer = $lexer;
        $this->mainNodeTraverser = $mainNodeTraverser;
        $this->rectorCollector = $rectorCollector;
        $this->cloningNodeTraverser = $cloningNodeTraverser;
        $this->shutdownNodeTraverser = $shutdownNodeTraverser;
        $this->standaloneTraverseNodeTraverser = $standaloneTraverseNodeTraverser;
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

        return $this->processFile($file);
    }

    /**
     * See https://github.com/nikic/PHP-Parser/issues/344#issuecomment-298162516.
     */
    public function processFile(SplFileInfo $file): string
    {
        $oldStmts = $this->parser->parseFile($file->getRealPath());
        $oldTokens = $this->lexer->getTokens();
        $newStmts = $this->cloningNodeTraverser->traverse($oldStmts);

        $newStmts = $this->standaloneTraverseNodeTraverser->traverse($newStmts);

        $newStmts = $this->mainNodeTraverser->traverse($newStmts);
        $newStmts = $this->shutdownNodeTraverser->traverse($newStmts);

        return $this->codeStyledPrinter->printToString($newStmts, $oldStmts, $oldTokens);
    }
}
