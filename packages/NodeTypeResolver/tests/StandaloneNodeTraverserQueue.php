<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests;

use PhpParser\Node;
use PhpParser\Parser;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Symfony\Component\Finder\SplFileInfo;

final class StandaloneNodeTraverserQueue
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;

    public function __construct(Parser $parser, NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator)
    {
        $this->parser = $parser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
    }

    /**
     * @return Node[]
     */
    public function processFileInfo(SplFileInfo $fileInfo): array
    {
        $nodes = $this->parser->parse($fileInfo->getContents());

        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($nodes, $fileInfo->getRealPath());
    }
}
