<?php declare(strict_types=1);

namespace Rector\Testing\Application;

use PhpParser\Lexer;
use PhpParser\NodeVisitor;
use Rector\Contract\Parser\ParserInterface;
use Rector\NodeTraverser\CloningNodeTraverser;
use Rector\NodeTraverser\ConnectorNodeTraverser;
use Rector\NodeTraverser\MainNodeTraverser;
use Rector\NodeTraverser\NamingNodeTraverser;
use Rector\NodeTraverser\ShutdownNodeTraverser;
use Rector\NodeTraverser\ClassLikeTypeResolvingNodeTraverser;
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
     * @var ClassLikeTypeResolvingNodeTraverser
     */
    private $classLikeTypeResolvingNodeTraverser;

    /**
     * @var ShutdownNodeTraverser
     */
    private $shutdownNodeTraverser;

    /**
     * @var NamingNodeTraverser
     */
    private $namingNodeTraverser;

    /**
     * @var ConnectorNodeTraverser
     */
    private $connectorNodeTraverser;

    public function __construct(
        CloningNodeTraverser $cloningNodeTraverser,
        ParserInterface $parser,
        FormatPerservingPrinter $codeStyledPrinter,
        Lexer $lexer,
        MainNodeTraverser $mainNodeTraverser,
        RectorCollector $rectorCollector,
        ClassLikeTypeResolvingNodeTraverser $classLikeTypeResolvingNodeTraverser,
        ShutdownNodeTraverser $shutdownNodeTraverser,
        NamingNodeTraverser $namingNodeTraverser,
        ConnectorNodeTraverser $connectorNodeTraverser
    ) {
        $this->parser = $parser;
        $this->codeStyledPrinter = $codeStyledPrinter;
        $this->lexer = $lexer;
        $this->mainNodeTraverser = $mainNodeTraverser;
        $this->rectorCollector = $rectorCollector;
        $this->cloningNodeTraverser = $cloningNodeTraverser;
        $this->classLikeTypeResolvingNodeTraverser = $classLikeTypeResolvingNodeTraverser;
        $this->shutdownNodeTraverser = $shutdownNodeTraverser;
        $this->namingNodeTraverser = $namingNodeTraverser;
        $this->connectorNodeTraverser = $connectorNodeTraverser;
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

        // @todo: introduce Traverser queue?
        $newStmts = $this->namingNodeTraverser->traverse($newStmts);
        $newStmts = $this->connectorNodeTraverser->traverse($newStmts);
        $newStmts = $this->classLikeTypeResolvingNodeTraverser->traverse($newStmts);
        $newStmts = $this->mainNodeTraverser->traverse($newStmts);
        $newStmts = $this->shutdownNodeTraverser->traverse($newStmts);

        return $this->codeStyledPrinter->printToString($newStmts, $oldStmts, $oldTokens);
    }
}
