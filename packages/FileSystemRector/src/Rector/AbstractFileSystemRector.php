<?php declare(strict_types=1);

namespace Rector\FileSystemRector\Rector;

use PhpParser\Lexer;
use PhpParser\Node;
use Rector\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Configuration\Configuration;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\PhpParser\Parser\Parser;
use Rector\PhpParser\Printer\FormatPerservingPrinter;
use Rector\Rector\AbstractRectorTrait;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

abstract class AbstractFileSystemRector implements FileSystemRectorInterface
{
    use AbstractRectorTrait;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var FormatPerservingPrinter
     */
    private $formatPerservingPrinter;

    /**
     * @var NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;

    /**
     * @var Node[]
     */
    private $oldStmts = [];

    /**
     * @var RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;

    /**
     * @required
     */
    public function setAbstractFileSystemRectorDependencies(
        Parser $parser,
        Lexer $lexer,
        FormatPerservingPrinter $formatPerservingPrinter,
        NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        Configuration $configuration
    ): void {
        $this->parser = $parser;
        $this->lexer = $lexer;
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->configuration = $configuration;
    }

    /**
     * @return Node[]
     */
    protected function parseFileInfoToNodes(SmartFileInfo $smartFileInfo): array
    {
        $oldStmts = $this->parser->parseFile($smartFileInfo->getRealPath());
        $this->oldStmts = $oldStmts;
        // needed for format preserving
        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile(
            $oldStmts,
            $smartFileInfo->getRealPath()
        );
    }

    /**
     * @param Node[] $nodes
     */
    protected function printNodesToFilePath(array $nodes, string $fileDestination): void
    {
        $fileContent = $this->formatPerservingPrinter->printToString(
            $nodes,
            $this->oldStmts,
            $this->lexer->getTokens()
        );

        $this->addFile($fileDestination, $fileContent);
    }

    protected function removeFile(SmartFileInfo $smartFileInfo): void
    {
        $this->removedAndAddedFilesCollector->removeFile($smartFileInfo);
    }

    protected function addFile(string $filePath, string $content): void
    {
        $this->removedAndAddedFilesCollector->addFileWithContent($filePath, $content);
    }
}
