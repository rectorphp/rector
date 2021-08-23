<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use PhpParser\Lexer;
use Rector\ChangesReporting\Collector\AffectedFilesCollector;
use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
final class FileProcessor
{
    /**
     * @var \Rector\ChangesReporting\Collector\AffectedFilesCollector
     */
    private $affectedFilesCollector;
    /**
     * @var \PhpParser\Lexer
     */
    private $lexer;
    /**
     * @var \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;
    /**
     * @var \Rector\Core\PhpParser\Parser\Parser
     */
    private $parser;
    /**
     * @var \Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser
     */
    private $rectorNodeTraverser;
    public function __construct(\Rector\ChangesReporting\Collector\AffectedFilesCollector $affectedFilesCollector, \PhpParser\Lexer $lexer, \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, \Rector\Core\PhpParser\Parser\Parser $parser, \Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser $rectorNodeTraverser)
    {
        $this->affectedFilesCollector = $affectedFilesCollector;
        $this->lexer = $lexer;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->parser = $parser;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
    }
    public function parseFileInfoToLocalCache(\Rector\Core\ValueObject\Application\File $file) : void
    {
        // store tokens by absolute path, so we don't have to print them right now
        $smartFileInfo = $file->getSmartFileInfo();
        $oldStmts = $this->parser->parseFileInfo($smartFileInfo);
        $oldTokens = $this->lexer->getTokens();
        // @todo may need tweak to refresh PHPStan types to avoid issue like in https://github.com/rectorphp/rector/issues/6561
        $newStmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $oldStmts);
        $file->hydrateStmtsAndTokens($newStmts, $oldStmts, $oldTokens);
    }
    public function refactor(\Rector\Core\ValueObject\Application\File $file) : void
    {
        $newStmts = $this->rectorNodeTraverser->traverse($file->getNewStmts());
        $file->changeNewStmts($newStmts);
        $this->affectedFilesCollector->removeFromList($file);
        while ($otherTouchedFile = $this->affectedFilesCollector->getNext()) {
            $this->refactor($otherTouchedFile);
        }
    }
}
