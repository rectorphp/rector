<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use Rector\ChangesReporting\Collector\AffectedFilesCollector;
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
    public function __construct(\Rector\ChangesReporting\Collector\AffectedFilesCollector $affectedFilesCollector, \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, \Rector\Core\PhpParser\Parser\RectorParser $rectorParser, \Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser $rectorNodeTraverser)
    {
        $this->affectedFilesCollector = $affectedFilesCollector;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->rectorParser = $rectorParser;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
    }
    public function parseFileInfoToLocalCache(\Rector\Core\ValueObject\Application\File $file) : void
    {
        // store tokens by absolute path, so we don't have to print them right now
        $smartFileInfo = $file->getSmartFileInfo();
        $stmtsAndTokens = $this->rectorParser->parseFileToStmtsAndTokens($smartFileInfo);
        $oldStmts = $stmtsAndTokens->getStmts();
        $oldTokens = $stmtsAndTokens->getTokens();
        // @todo may need tweak to refresh PHPStan types to avoid issue like in https://github.com/rectorphp/rector/issues/6561
        $newStmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $oldStmts);
        $file->hydrateStmtsAndTokens($newStmts, $oldStmts, $oldTokens);
    }
    public function refactor(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : void
    {
        $newStmts = $this->rectorNodeTraverser->traverse($file->getNewStmts());
        $file->changeNewStmts($newStmts);
        $this->affectedFilesCollector->removeFromList($file);
        while ($otherTouchedFile = $this->affectedFilesCollector->getNext()) {
            $this->refactor($otherTouchedFile, $configuration);
        }
    }
}
