<?php

declare (strict_types=1);
namespace Rector\FileSystemRector\Parser;

use RectorPrefix202312\Nette\Utils\FileSystem;
use PhpParser\Node\Stmt;
use Rector\Core\PhpParser\Parser\RectorParser;
use Rector\Core\Provider\CurrentFileProvider;
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
     * @var \Rector\Core\PhpParser\Parser\RectorParser
     */
    private $rectorParser;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    public function __construct(NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, RectorParser $rectorParser, CurrentFileProvider $currentFileProvider)
    {
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->rectorParser = $rectorParser;
        $this->currentFileProvider = $currentFileProvider;
    }
    /**
     * @api tests only
     * @return Stmt[]
     */
    public function parseFileInfoToNodesAndDecorate(string $filePath) : array
    {
        $fileContent = FileSystem::read($filePath);
        $stmts = $this->rectorParser->parseString($fileContent);
        $file = new File($filePath, $fileContent);
        $stmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($filePath, $stmts);
        $file->hydrateStmtsAndTokens($stmts, $stmts, []);
        $this->currentFileProvider->setFile($file);
        return $stmts;
    }
}
