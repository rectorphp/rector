<?php

declare (strict_types=1);
namespace Rector\FileSystemRector\Parser;

use PhpParser\Node\Stmt;
use Rector\Core\PhpParser\NodeTraverser\FileWithoutNamespaceNodeTraverser;
use Rector\Core\PhpParser\Parser\RectorParser;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\Testing\Fixture\FixtureTempFileDumper;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * Only for testing, @todo move to testing
 */
final class FileInfoParser
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\NodeTraverser\FileWithoutNamespaceNodeTraverser
     */
    private $fileWithoutNamespaceNodeTraverser;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\RectorParser
     */
    private $rectorParser;
    public function __construct(NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, FileWithoutNamespaceNodeTraverser $fileWithoutNamespaceNodeTraverser, RectorParser $rectorParser)
    {
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->fileWithoutNamespaceNodeTraverser = $fileWithoutNamespaceNodeTraverser;
        $this->rectorParser = $rectorParser;
    }
    /**
     * @return Stmt[]
     * @param \Symplify\SmartFileSystem\SmartFileInfo|string $smartFileInfo
     */
    public function parseFileInfoToNodesAndDecorate($smartFileInfo) : array
    {
        if (\is_string($smartFileInfo)) {
            $smartFileInfo = FixtureTempFileDumper::dump($smartFileInfo);
        }
        $stmts = $this->rectorParser->parseFile($smartFileInfo);
        $stmts = $this->fileWithoutNamespaceNodeTraverser->traverse($stmts);
        $file = new File($smartFileInfo, $smartFileInfo->getContents());
        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $stmts);
    }
}
