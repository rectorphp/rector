<?php

declare (strict_types=1);
namespace Rector\FileSystemRector\Parser;

use PhpParser\Node;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Symplify\SmartFileSystem\SmartFileInfo;
final class FileInfoParser
{
    /**
     * @var \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;
    /**
     * @var \Rector\Core\PhpParser\Parser\Parser
     */
    private $parser;
    public function __construct(\Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, \Rector\Core\PhpParser\Parser\Parser $parser)
    {
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->parser = $parser;
    }
    /**
     * @return Node[]
     */
    public function parseFileInfoToNodesAndDecorate(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : array
    {
        $oldStmts = $this->parser->parseFileInfo($smartFileInfo);
        $file = new \Rector\Core\ValueObject\Application\File($smartFileInfo, $smartFileInfo->getContents());
        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $oldStmts);
    }
}
