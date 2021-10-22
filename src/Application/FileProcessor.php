<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Nette\Utils\Strings;
use PhpParser\Lexer;
use PhpParser\Node;
use Rector\ChangesReporting\Collector\AffectedFilesCollector;
use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;

final class FileProcessor
{
    /**
     * @var string
     * @see https://regex101.com/r/ozPuC9/1
     */
    private const TEMPLATE_EXTENDS_REGEX = '#(\*|\/\/)\s+\@template-extends\s+\\\\?\w+#';

    public function __construct(
        private AffectedFilesCollector $affectedFilesCollector,
        private Lexer $lexer,
        private NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        private Parser $parser,
        private RectorNodeTraverser $rectorNodeTraverser,
        private BetterStandardPrinter $betterStandardPrinter
    ) {
    }

    public function parseFileInfoToLocalCache(File $file): void
    {
        // store tokens by absolute path, so we don't have to print them right now
        $smartFileInfo = $file->getSmartFileInfo();
        $oldStmts = $this->parser->parseFileInfo($smartFileInfo);
        $oldTokens = $this->lexer->getTokens();

        /**
         * Tweak PHPStan internal issue for has @template-extends that cause endless loop in the process
         *
         * @see https://github.com/phpstan/phpstan/issues/3865
         * @see https://github.com/rectorphp/rector/issues/6758
         */
        if ($this->isTemplateExtendsInSource($oldStmts)) {
            $file->hydrateStmtsAndTokens($oldStmts, $oldStmts, $oldTokens);
            return;
        }

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

    /**
     * @param Node[] $nodes
     */
    private function isTemplateExtendsInSource(array $nodes): bool
    {
        $print = $this->betterStandardPrinter->print($nodes);
        return (bool) Strings::match($print, self::TEMPLATE_EXTENDS_REGEX);
    }
}
