<?php

declare (strict_types=1);
namespace Rector\FileSystemRector\Parser;

use PhpParser\Node;
use Rector\Core\PhpParser\Parser\RectorParser;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Symplify\SmartFileSystem\SmartFileInfo;
final class FileInfoParser
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\RectorParser
     */
    private $rectorParser;
    public function __construct(\Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, \Rector\Core\PhpParser\Parser\RectorParser $rectorParser)
    {
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->rectorParser = $rectorParser;
    }
    /**
     * @return Node[]
     */
    public function parseFileInfoToNodesAndDecorate(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : array
    {
        $stmts = $this->rectorParser->parseFile($smartFileInfo);
        $file = new \Rector\Core\ValueObject\Application\File($smartFileInfo, $smartFileInfo->getContents());
        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $stmts);
    }
}
