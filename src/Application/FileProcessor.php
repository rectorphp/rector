<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use PhpParser\Lexer;
use Rector\ChangesReporting\Collector\AffectedFilesCollector;
use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;

final class FileProcessor
{
    public function __construct(
        private AffectedFilesCollector $affectedFilesCollector,
        private Lexer $lexer,
        private NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        private Parser $parser,
        private RectorNodeTraverser $rectorNodeTraverser
    ) {
    }

    public function parseFileInfoToLocalCache(File $file): void
    {
        // store tokens by absolute path, so we don't have to print them right now
        $smartFileInfo = $file->getSmartFileInfo();
        $oldStmts = $this->parser->parseFileInfo($smartFileInfo);
        $oldTokens = $this->lexer->getTokens();

        // @todo may need tweak to refresh PHPStan types to avoid issue like in https://github.com/rectorphp/rector/issues/6561
        $newStmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $oldStmts);
        $file->hydrateStmtsAndTokens($newStmts, $oldStmts, $oldTokens);
    }

    public function refactor(File $file): void
    {
        $newStmts = $this->rectorNodeTraverser->traverse($file->getNewStmts());
        $file->changeNewStmts($newStmts);

        $this->affectedFilesCollector->removeFromList($file);
        while ($otherTouchedFile = $this->affectedFilesCollector->getNext()) {
            $this->refactor($otherTouchedFile);
        }
    }
}
