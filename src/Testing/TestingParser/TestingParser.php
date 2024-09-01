<?php

declare (strict_types=1);
namespace Rector\Testing\TestingParser;

use RectorPrefix202409\Nette\Utils\FileSystem;
use PhpParser\Node;
use Rector\Application\Provider\CurrentFileProvider;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\PhpParser\Parser\RectorParser;
use Rector\ValueObject\Application\File;
/**
 * @api
 */
final class TestingParser
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Parser\RectorParser
     */
    private $rectorParser;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;
    /**
     * @readonly
     * @var \Rector\Application\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider
     */
    private $dynamicSourceLocatorProvider;
    public function __construct(RectorParser $rectorParser, NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, CurrentFileProvider $currentFileProvider, DynamicSourceLocatorProvider $dynamicSourceLocatorProvider)
    {
        $this->rectorParser = $rectorParser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->currentFileProvider = $currentFileProvider;
        $this->dynamicSourceLocatorProvider = $dynamicSourceLocatorProvider;
    }
    public function parseFilePathToFile(string $filePath) : File
    {
        // needed for PHPStan reflection, as it caches the last processed file
        $this->dynamicSourceLocatorProvider->setFilePath($filePath);
        $fileContent = FileSystem::read($filePath);
        $file = new File($filePath, $fileContent);
        $stmts = $this->rectorParser->parseString($fileContent);
        $stmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($filePath, $stmts);
        $file->hydrateStmtsAndTokens($stmts, $stmts, []);
        $this->currentFileProvider->setFile($file);
        return $file;
    }
    /**
     * @return Node[]
     */
    public function parseFileToDecoratedNodes(string $filePath) : array
    {
        // needed for PHPStan reflection, as it caches the last processed file
        $this->dynamicSourceLocatorProvider->setFilePath($filePath);
        $fileContent = FileSystem::read($filePath);
        $stmts = $this->rectorParser->parseString($fileContent);
        $file = new File($filePath, $fileContent);
        $stmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($filePath, $stmts);
        $file->hydrateStmtsAndTokens($stmts, $stmts, []);
        $this->currentFileProvider->setFile($file);
        return $stmts;
    }
}
