<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Core\PhpParser\Parser\RectorParser;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\PostRector\Application\PostFileProcessor;
final class FileProcessor
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\RectorParser
     */
    private $rectorParser;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser
     */
    private $rectorNodeTraverser;
    /**
     * @readonly
     * @var \Rector\PostRector\Application\PostFileProcessor
     */
    private $postFileProcessor;
    public function __construct(NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, RectorParser $rectorParser, RectorNodeTraverser $rectorNodeTraverser, PostFileProcessor $postFileProcessor)
    {
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->rectorParser = $rectorParser;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->postFileProcessor = $postFileProcessor;
    }
    public function parseFileInfoToLocalCache(File $file) : void
    {
        // store tokens by absolute path, so we don't have to print them right now
        $stmtsAndTokens = $this->rectorParser->parseFileToStmtsAndTokens($file->getFilePath());
        $oldStmts = $stmtsAndTokens->getStmts();
        $oldTokens = $stmtsAndTokens->getTokens();
        $newStmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $oldStmts);
        $file->hydrateStmtsAndTokens($newStmts, $oldStmts, $oldTokens);
    }
    public function process(File $file) : void
    {
        $newStmts = $this->rectorNodeTraverser->traverse($file->getNewStmts());
        // apply post rectors
        $postNewStmts = $this->postFileProcessor->traverse($newStmts);
        // this is needed for new tokens added in "afterTraverse()"
        $file->changeNewStmts($postNewStmts);
    }
}
