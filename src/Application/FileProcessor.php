<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use Rector\ChangesReporting\Collector\AffectedFilesCollector;
use Rector\Core\PhpParser\NodeTraverser\FileWithoutNamespaceNodeTraverser;
use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Core\PhpParser\Parser\RectorParser;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
final class FileProcessor
{
    /**
     * @readonly
     * @var \Rector\ChangesReporting\Collector\AffectedFilesCollector
     */
    private $affectedFilesCollector;
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
     * @var \Rector\Core\PhpParser\NodeTraverser\FileWithoutNamespaceNodeTraverser
     */
    private $fileWithoutNamespaceNodeTraverser;
    public function __construct(AffectedFilesCollector $affectedFilesCollector, NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, RectorParser $rectorParser, RectorNodeTraverser $rectorNodeTraverser, FileWithoutNamespaceNodeTraverser $fileWithoutNamespaceNodeTraverser)
    {
        $this->affectedFilesCollector = $affectedFilesCollector;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->rectorParser = $rectorParser;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->fileWithoutNamespaceNodeTraverser = $fileWithoutNamespaceNodeTraverser;
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
    public function refactor(File $file, Configuration $configuration) : void
    {
        $newStmts = $this->fileWithoutNamespaceNodeTraverser->traverse($file->getNewStmts());
        $newStmts = $this->rectorNodeTraverser->traverse($newStmts);
        $file->changeNewStmts($newStmts);
        $this->affectedFilesCollector->removeFromList($file);
        while (($otherTouchedFile = $this->affectedFilesCollector->getNext()) instanceof File) {
            $this->refactor($otherTouchedFile, $configuration);
        }
    }
}
