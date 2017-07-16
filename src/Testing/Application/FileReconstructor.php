<?php declare(strict_types=1);

namespace Rector\Testing\Application;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Rector\Contract\Dispatcher\ReconstructorInterface;
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

    public function __construct(Parser $parser, CodeStyledPrinter $codeStyledPrinter, Lexer $lexer)
    {
        $this->parser = $parser;
        $this->codeStyledPrinter = $codeStyledPrinter;
        $this->lexer = $lexer;
    }

    # ref: https://github.com/nikic/PHP-Parser/issues/344#issuecomment-298162516
    public function processFileWithReconstructor(SplFileInfo $file, ReconstructorInterface $reconstructor): string
    {
        $fileContent = file_get_contents($file->getRealPath());

        /** @var Node[] $nodes */
        $oldStmts = $this->parser->parse($fileContent);

        // before recontruct event?

        // keep format printer
        $oldTokens = $this->lexer->getTokens();
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new CloningVisitor);
        $newStmts = $traverser->traverse($oldStmts);

        foreach ($oldStmts as $node) {
            if ($reconstructor->isCandidate($node)) {
                $reconstructor->reconstruct($node);
            }

        }

        // after reconstruct evnet?

        return $this->codeStyledPrinter->printToString($oldStmts, $newStmts, $oldTokens);
    }
}
