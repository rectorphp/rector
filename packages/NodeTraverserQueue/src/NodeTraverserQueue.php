<?php declare(strict_types=1);

namespace Rector\NodeTraverserQueue;

use PhpParser\Lexer;
use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\NodeTraverser\StandaloneTraverseNodeTraverser;
use Rector\NodeTypeResolver\Configuration\CurrentFileProvider;
use Rector\Parser\Parser;
use Symfony\Component\Finder\SplFileInfo;

final class NodeTraverserQueue
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var RectorNodeTraverser
     */
    private $rectorNodeTraverser;

    /**
     * @var StandaloneTraverseNodeTraverser
     */
    private $standaloneTraverseNodeTraverser;

    /**
     * @var CurrentFileProvider
     */
    private $currentFileProvider;

    public function __construct(
        Parser $parser,
        Lexer $lexer,
        RectorNodeTraverser $rectorNodeTraverser,
        StandaloneTraverseNodeTraverser $standaloneTraverseNodeTraverser,
        CurrentFileProvider $currentFileProvider
    ) {
        $this->parser = $parser;
        $this->lexer = $lexer;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->standaloneTraverseNodeTraverser = $standaloneTraverseNodeTraverser;
        $this->currentFileProvider = $currentFileProvider;
    }

    /**
     * @return mixed[]
     */
    public function processFileInfo(SplFileInfo $fileInfo): array
    {
        $this->currentFileProvider->setCurrentFile($fileInfo);

        $oldStmts = $this->parser->parseFile($fileInfo->getRealPath());
        $oldTokens = $this->lexer->getTokens();

        $newStmts = $this->standaloneTraverseNodeTraverser->traverse($oldStmts);
        $newStmts = $this->rectorNodeTraverser->traverse($newStmts);

        return [$newStmts, $oldStmts, $oldTokens];
    }
}
