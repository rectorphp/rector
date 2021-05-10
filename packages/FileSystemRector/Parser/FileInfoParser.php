<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\Parser;

use PhpParser\Node;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FileInfoParser
{
    public function __construct(
        private NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        private Parser $parser
    ) {
    }

    /**
     * @return Node[]
     */
    public function parseFileInfoToNodesAndDecorate(SmartFileInfo $smartFileInfo): array
    {
        $oldStmts = $this->parser->parseFileInfo($smartFileInfo);
        $file = new File($smartFileInfo, $smartFileInfo->getContents());

        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $oldStmts, $smartFileInfo);
    }
}
