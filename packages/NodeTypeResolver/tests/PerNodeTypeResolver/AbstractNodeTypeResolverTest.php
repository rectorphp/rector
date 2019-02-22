<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver;

use PhpParser\Node;
use Rector\Configuration\Option;
use Rector\HttpKernel\RectorKernel;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Parser\Parser;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

abstract class AbstractNodeTypeResolverTest extends AbstractKernelTestCase
{
    /**
     * @var NodeTypeResolver
     */
    protected $nodeTypeResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->betterNodeFinder = self::$container->get(BetterNodeFinder::class);
        $this->parameterProvider = self::$container->get(ParameterProvider::class);
        $this->nodeTypeResolver = self::$container->get(NodeTypeResolver::class);
        $this->parser = self::$container->get(Parser::class);
        $this->nodeScopeAndMetadataDecorator = self::$container->get(NodeScopeAndMetadataDecorator::class);
    }

    /**
     * @return Node[]
     */
    protected function getNodesForFileOfType(string $file, string $type): array
    {
        $nodes = $this->getNodesForFile($file);

        return $this->betterNodeFinder->findInstanceOf($nodes, $type);
    }

    /**
     * @return Node[]
     */
    private function getNodesForFile(string $file): array
    {
        $smartFileInfo = new SmartFileInfo($file);

        $this->parameterProvider->changeParameter(Option::SOURCE, [$file]);

        $nodes = $this->parser->parseFile($smartFileInfo->getRealPath());

        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($nodes, $smartFileInfo->getRealPath());
    }
}
