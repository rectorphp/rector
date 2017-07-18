<?php declare(strict_types=1);

namespace Rector\Testing\Application;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use Rector\Printer\CodeStyledPrinter;
use SplFileInfo;

final class FileReconstructor
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var CodeStyledPrinter
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

    public function __construct(Parser $parser, CodeStyledPrinter $codeStyledPrinter, Lexer $lexer, NodeTraverser $nodeTraverser)
    {
        $this->parser = $parser;
        $this->codeStyledPrinter = $codeStyledPrinter;
        $this->lexer = $lexer;
        $this->nodeTraverser = $nodeTraverser;
    }

    # ref: https://github.com/nikic/PHP-Parser/issues/344#issuecomment-298162516
    public function processFileWithReconstructor(SplFileInfo $file, NodeVisitor $nodeVisitor): string
    {
        $fileContent = file_get_contents($file->getRealPath());

        /** @var Node[] $nodes */
        $oldStmts = $this->parser->parse($fileContent);

        // before recontruct event?

        // keep format printer
        $oldTokens = $this->lexer->getTokens();

        $this->nodeTraverser->addVisitor($nodeVisitor);
        $newStmts = $this->nodeTraverser->traverse($oldStmts);

        return $this->codeStyledPrinter->printToString($oldStmts, $newStmts, $oldTokens);
    }
}
