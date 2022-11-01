<?php

declare (strict_types=1);
namespace Rector\FileSystemRector\Parser;

use RectorPrefix202211\Nette\Utils\FileSystem;
use PhpParser\Node\Stmt;
use Rector\Core\PhpParser\NodeTraverser\FileWithoutNamespaceNodeTraverser;
use Rector\Core\PhpParser\Parser\RectorParser;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
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
     */
    public function parseFileInfoToNodesAndDecorate(string $filePath) : array
    {
        $stmts = $this->rectorParser->parseFile($filePath);
        $stmts = $this->fileWithoutNamespaceNodeTraverser->traverse($stmts);
        $file = new File($filePath, FileSystem::read($filePath));
        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $stmts);
    }
}
