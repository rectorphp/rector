<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\Rector;

use Nette\Utils\Strings;
use PhpParser\Lexer;
use PhpParser\Node;
use Rector\Autodiscovery\ValueObject\NodesWithFileDestination;
use Rector\Core\Application\FileProcessor;
use Rector\Core\Application\TokensByFilePathStorage;
use Rector\Core\Configuration\Configuration;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\PhpParser\Printer\FormatPerservingPrinter;
use Rector\Core\Rector\AbstractRector\AbstractRectorTrait;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\PostRector\Application\PostFileProcessor;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractFileSystemRector implements FileSystemRectorInterface
{
    use AbstractRectorTrait;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var ParameterProvider
     */
    protected $parameterProvider;

    /**
     * @var Node[]
     */
    private $oldStmts = [];

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var FormatPerservingPrinter
     */
    private $formatPerservingPrinter;

    /**
     * @var PostFileProcessor
     */
    private $postFileProcessor;

    /**
     * @var RenamedClassesCollector
     */
    private $renamedClassesCollector;

    /**
     * @var TokensByFilePathStorage
     */
    private $tokensByFilePathStorage;

    /**
     * @var FileProcessor
     */
    private $fileProcessor;

    /**
     * @required
     */
    public function autowireAbstractFileSystemRector(
        Lexer $lexer,
        FormatPerservingPrinter $formatPerservingPrinter,
        Configuration $configuration,
        BetterStandardPrinter $betterStandardPrinter,
        ParameterProvider $parameterProvider,
        PostFileProcessor $postFileProcessor,
        RenamedClassesCollector $renamedClassesCollector,
        TokensByFilePathStorage $tokensByFilePathStorage,
        FileProcessor $fileProcessor
    ): void {
        $this->lexer = $lexer;
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->configuration = $configuration;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->parameterProvider = $parameterProvider;
        $this->postFileProcessor = $postFileProcessor;
        $this->renamedClassesCollector = $renamedClassesCollector;
        $this->tokensByFilePathStorage = $tokensByFilePathStorage;
        $this->fileProcessor = $fileProcessor;
    }

    protected function addClassRename(string $oldClass, string $newClass): void
    {
        $this->renamedClassesCollector->addClassRename($oldClass, $newClass);
    }

    /**
     * @return Node[]
     */
    protected function parseFileInfoToNodes(SmartFileInfo $smartFileInfo): array
    {
        if (! $this->tokensByFilePathStorage->hasForFileInfo($smartFileInfo)) {
            $this->fileProcessor->parseFileInfoToLocalCache($smartFileInfo);
        }

        $parsedStmtsAndTokens = $this->tokensByFilePathStorage->getForFileInfo($smartFileInfo);

        // needed for format preserving
        $this->oldStmts = $parsedStmtsAndTokens->getOldStmts();

        return $parsedStmtsAndTokens->getNewStmts();
    }

    /**
     * @param Node[] $nodes
     */
    protected function printNodesToFilePath(array $nodes, string $fileDestination): void
    {
        $nodes = $this->postFileProcessor->traverse($nodes);

        $fileContent = $this->formatPerservingPrinter->printToString(
            $nodes,
            $this->oldStmts,
            $this->lexer->getTokens()
        );

        $this->addFile($fileDestination, $fileContent);
    }

    /**
     * @param Node[] $nodes
     */
    protected function printNewNodesToFilePath(array $nodes, string $fileDestination): void
    {
        $nodes = $this->postFileProcessor->traverse($nodes);

        // re-index keys from 0
        $nodes = array_values($nodes);

        $fileContent = $this->betterStandardPrinter->prettyPrintFile($nodes);
        $fileContent = $this->resolveLastEmptyLine($fileContent);

        $this->addFile($fileDestination, $fileContent);
    }

    protected function printNodesWithFileDestination(NodesWithFileDestination $nodesWithFileDestination): void
    {
        $this->addNodesWithFileDestination($nodesWithFileDestination);
    }

    /**
     * Add empty line in the end, if it is in the original tokens
     */
    private function resolveLastEmptyLine(string $prettyPrintContent): string
    {
        $tokens = $this->lexer->getTokens();
        $lastToken = array_pop($tokens);
        if ($lastToken && Strings::contains($lastToken[1], "\n")) {
            $prettyPrintContent = trim($prettyPrintContent) . PHP_EOL;
        }

        return $prettyPrintContent;
    }
}
