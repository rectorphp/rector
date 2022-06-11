<?php

declare (strict_types=1);
namespace Rector\Testing\TestingParser;

use PhpParser\Node;
use Rector\Core\Configuration\Option;
use Rector\Core\PhpParser\Parser\RectorParser;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use RectorPrefix202206\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix202206\Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @api
 */
final class TestingParser
{
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
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
        $smartFileInfo = new SmartFileInfo($filePath);
        $file = new File($smartFileInfo, $smartFileInfo->getContents());
        $stmts = $this->rectorParser->parseFile($smartFileInfo);
        $file->hydrateStmtsAndTokens($stmts, $stmts, []);
        return $file;
    }
    /**
     * @return Node[]
     */
    public function parseFileToDecoratedNodes(string $filepath) : array
    {
        // autoload file
        require_once $filepath;
        $smartFileInfo = new SmartFileInfo($filepath);
        $this->parameterProvider->changeParameter(Option::SOURCE, [$filepath]);
        $nodes = $this->rectorParser->parseFile($smartFileInfo);
        $file = new File($smartFileInfo, $smartFileInfo->getContents());
        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $nodes);
    }
}
