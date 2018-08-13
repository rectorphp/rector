<?php declare(strict_types=1);

namespace Rector\NodeTraverserQueue;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\NodeTraverser\StandaloneTraverseNodeTraverser;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeScopeResolver;
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
     * @var NodeScopeResolver
     */
    private $nodeScopeResolver;

    public function __construct(
        Parser $parser,
        Lexer $lexer,
        RectorNodeTraverser $rectorNodeTraverser,
        StandaloneTraverseNodeTraverser $standaloneTraverseNodeTraverser,
        NodeScopeResolver $nodeScopeResolver
    ) {
        $this->parser = $parser;
        $this->lexer = $lexer;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->standaloneTraverseNodeTraverser = $standaloneTraverseNodeTraverser;
        $this->nodeScopeResolver = $nodeScopeResolver;
    }

    /**
     * @return mixed[]
     */
    public function processFileInfo(SplFileInfo $fileInfo): array
    {
        $oldStmts = $this->parser->parseFile($fileInfo->getRealPath());
        $oldTokens = $this->lexer->getTokens();

        $newStmts = $oldStmts;

        // @todo use NodeTypeResolverNodeTraverses
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());
        $newStmts = $nodeTraverser->traverse($newStmts);
        $this->nodeScopeResolver->processNodes($newStmts, $fileInfo);

        $newStmts = $this->standaloneTraverseNodeTraverser->traverse($newStmts);
        $newStmts = $this->rectorNodeTraverser->traverse($newStmts);

        return [$newStmts, $oldStmts, $oldTokens];
    }
}
