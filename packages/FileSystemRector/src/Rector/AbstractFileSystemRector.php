<?php declare(strict_types=1);

namespace Rector\FileSystemRector\Rector;

use PhpParser\Lexer;
use PhpParser\Node;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\PhpParser\Parser\Parser;
use Rector\PhpParser\Printer\FormatPerservingPrinter;
use Rector\Rector\AbstractRectorTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

abstract class AbstractFileSystemRector implements FileSystemRectorInterface
{
    use AbstractRectorTrait;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var FormatPerservingPrinter
     */
    protected $formatPerservingPrinter;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;

    /**
     * @var Node[]
     */
    private $oldStmts = [];

    /**
     * @required
     */
    public function setAbstractFileSystemRectorDependencies(
        Parser $parser,
        Lexer $lexer,
        FormatPerservingPrinter $formatPerservingPrinter,
        Filesystem $filesystem,
        NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator
    ): void {
        $this->parser = $parser;
        $this->lexer = $lexer;
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->filesystem = $filesystem;
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
    protected function printNodesToString(array $nodes): string
    {
        return $this->formatPerservingPrinter->printToString($nodes, $this->oldStmts, $this->lexer->getTokens());
    }

    /**
     * @param Node[] $nodes
     */
    protected function printNodesToFilePath(array $nodes, string $fileDestination): void
    {
        $fileContent = $this->printNodesToString($nodes);
        $this->filesystem->dumpFile($fileDestination, $fileContent);
    }
}
