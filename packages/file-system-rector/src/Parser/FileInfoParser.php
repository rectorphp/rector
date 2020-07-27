<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\Parser;

use PhpParser\Node;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FileInfoParser
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;

    public function __construct(NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, Parser $parser)
    {
        $this->parser = $parser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
    }

    /**
     * @return Node[]
     */
    public function parseFileInfoToNodesAndDecorate(SmartFileInfo $fileInfo): array
    {
        $oldStmts = $this->parser->parseFileInfo($fileInfo);

        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($oldStmts, $fileInfo);
    }

    /**
     * @return Node[]
     */
    public function parseFileInfoToNodesAndDecorateWithScope(SmartFileInfo $fileInfo): array
    {
        $oldStmts = $this->parser->parseFileInfo($fileInfo);

        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($oldStmts, $fileInfo, true);
    }
}
