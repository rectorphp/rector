<?php

declare (strict_types=1);
namespace Rector\Testing\TestingParser;

use RectorPrefix202307\Nette\Utils\FileSystem;
use PhpParser\Node;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Core\PhpParser\Parser\RectorParser;
use Rector\Core\Provider\CurrentFileProvider;
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
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    public function __construct(ParameterProvider $parameterProvider, RectorParser $rectorParser, NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, CurrentFileProvider $currentFileProvider)
    {
        $this->parameterProvider = $parameterProvider;
        $this->rectorParser = $rectorParser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->currentFileProvider = $currentFileProvider;
    }
    public function parseFilePathToFile(string $filePath) : File
    {
        $file = new File($filePath, FileSystem::read($filePath));
        $stmts = $this->rectorParser->parseFile($filePath);
        $stmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $stmts);
        $file->hydrateStmtsAndTokens($stmts, $stmts, []);
        $this->currentFileProvider->setFile($file);
        return $file;
    }
    /**
     * @return Node[]
     */
    public function parseFileToDecoratedNodes(string $filePath) : array
    {
        $this->parameterProvider->changeParameter(Option::SOURCE, [$filePath]);
        $stmts = $this->rectorParser->parseFile($filePath);
        $file = new File($filePath, FileSystem::read($filePath));
        $stmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $stmts);
        $file->hydrateStmtsAndTokens($stmts, $stmts, []);
        $this->currentFileProvider->setFile($file);
        return $stmts;
    }
}
