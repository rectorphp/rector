<?php

declare (strict_types=1);
namespace Rector\Testing\TestingParser;

use RectorPrefix202301\Nette\Utils\FileSystem;
use PhpParser\Node;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Core\PhpParser\Parser\RectorParser;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
/**
 * @api
 */
final class TestingParser
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\RectorParser
     */
    private $rectorParser;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;
    public function __construct(ParameterProvider $parameterProvider, RectorParser $rectorParser, NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator)
    {
        $this->parameterProvider = $parameterProvider;
        $this->rectorParser = $rectorParser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
    }
    public function parseFilePathToFile(string $filePath) : File
    {
        $file = new File($filePath, FileSystem::read($filePath));
        $stmts = $this->rectorParser->parseFile($filePath);
        $file->hydrateStmtsAndTokens($stmts, $stmts, []);
        return $file;
    }
    /**
     * @return Node[]
     */
    public function parseFileToDecoratedNodes(string $filePath) : array
    {
        // autoload file
        require_once $filePath;
        $this->parameterProvider->changeParameter(Option::SOURCE, [$filePath]);
        $nodes = $this->rectorParser->parseFile($filePath);
        $file = new File($filePath, FileSystem::read($filePath));
        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $nodes);
    }
}
