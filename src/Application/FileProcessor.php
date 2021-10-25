<?php

declare (strict_types=1);
namespace Rector\Core\Application;

use RectorPrefix20211025\Nette\Utils\Strings;
use PhpParser\Node;
use Rector\ChangesReporting\Collector\AffectedFilesCollector;
use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Core\PhpParser\Parser\RectorParser;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
final class FileProcessor
{
    /**
     * @var string
     * @see https://regex101.com/r/ozPuC9/1
     */
    public const TEMPLATE_EXTENDS_REGEX = '#(\\*|\\/\\/)\\s+\\@template-extends\\s+\\\\?\\w+#';
    /**
     * @var \Rector\ChangesReporting\Collector\AffectedFilesCollector
     */
    private $affectedFilesCollector;
    /**
     * @var \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;
    /**
     * @var \Rector\Core\PhpParser\Parser\RectorParser
     */
    private $rectorParser;
    /**
     * @var \Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser
     */
    private $rectorNodeTraverser;
    /**
     * @var \Rector\Core\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    public function __construct(\Rector\ChangesReporting\Collector\AffectedFilesCollector $affectedFilesCollector, \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, \Rector\Core\PhpParser\Parser\RectorParser $rectorParser, \Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser $rectorNodeTraverser, \Rector\Core\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter)
    {
        $this->affectedFilesCollector = $affectedFilesCollector;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->rectorParser = $rectorParser;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }
    public function parseFileInfoToLocalCache(\Rector\Core\ValueObject\Application\File $file) : void
    {
        // store tokens by absolute path, so we don't have to print them right now
        $smartFileInfo = $file->getSmartFileInfo();
        $stmtsAndTokens = $this->rectorParser->parseFileToStmtsAndTokens($smartFileInfo);
        $oldStmts = $stmtsAndTokens->getStmts();
        $oldTokens = $stmtsAndTokens->getTokens();
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
    public function refactor(\Rector\Core\ValueObject\Application\File $file) : void
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
    private function isTemplateExtendsInSource(array $nodes) : bool
    {
        $print = $this->betterStandardPrinter->print($nodes);
        return (bool) \RectorPrefix20211025\Nette\Utils\Strings::match($print, self::TEMPLATE_EXTENDS_REGEX);
    }
}
