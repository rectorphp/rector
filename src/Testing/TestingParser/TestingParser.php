<?php

declare (strict_types=1);
namespace Rector\Testing\TestingParser;

use RectorPrefix202512\Nette\Utils\FileSystem;
use PhpParser\Node;
use Rector\Application\Provider\CurrentFileProvider;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\PhpParser\Node\FileNode;
use Rector\PhpParser\Parser\RectorParser;
use Rector\ValueObject\Application\File;
/**
 * @api
 */
final class TestingParser
{
    /**
     * @readonly
     */
    private RectorParser $rectorParser;
    /**
     * @readonly
     */
    private NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator;
    /**
     * @readonly
     */
    private CurrentFileProvider $currentFileProvider;
    /**
     * @readonly
     */
    private DynamicSourceLocatorProvider $dynamicSourceLocatorProvider;
    public function __construct(RectorParser $rectorParser, NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, CurrentFileProvider $currentFileProvider, DynamicSourceLocatorProvider $dynamicSourceLocatorProvider)
    {
        $this->rectorParser = $rectorParser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->currentFileProvider = $currentFileProvider;
        $this->dynamicSourceLocatorProvider = $dynamicSourceLocatorProvider;
    }
    public function parseFilePathToFile(string $filePath): File
    {
        [$file, $stmts] = $this->parseToFileAndStmts($filePath);
        return $file;
    }
    /**
     * @return Node[]
     */
    public function parseFileToDecoratedNodes(string $filePath): array
    {
        [$file, $stmts] = $this->parseToFileAndStmts($filePath);
        return $stmts;
    }
    /**
     * @return array{0: File, 1: Node[]}
     */
    private function parseToFileAndStmts(string $filePath): array
    {
        // needed for PHPStan reflection, as it caches the last processed file
        $this->dynamicSourceLocatorProvider->setFilePath($filePath);
        $fileContent = FileSystem::read($filePath);
        $file = new File($filePath, $fileContent);
        $stmts = $this->rectorParser->parseString($fileContent);
        // wrap in FileNode to enable file-level rules
        $stmts = [new FileNode($stmts)];
        $stmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($filePath, $stmts);
        $file->hydrateStmtsAndTokens($stmts, $stmts, []);
        $this->currentFileProvider->setFile($file);
        return [$file, $stmts];
    }
}
