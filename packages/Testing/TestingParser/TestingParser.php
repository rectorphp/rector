<?php

declare(strict_types=1);

namespace Rector\Testing\TestingParser;

use PhpParser\Node;
use Rector\Core\Configuration\Option;
use Rector\Core\PhpParser\Parser\RectorParser;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @api
 */
final class TestingParser
{
    public function __construct(
        private ParameterProvider $parameterProvider,
        private RectorParser $rectorParser,
        private NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
    ) {
    }

    public function parseFilePathToFile(string $filePath): File
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
    public function parseFileToDecoratedNodes(string $file): array
    {
        // autoload file
        require_once $file;

        $smartFileInfo = new SmartFileInfo($file);
        $this->parameterProvider->changeParameter(Option::SOURCE, [$file]);

        $nodes = $this->rectorParser->parseFile($smartFileInfo);

        $file = new File($smartFileInfo, $smartFileInfo->getContents());
        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $nodes);
    }
}
