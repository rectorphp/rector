<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver;

use PhpParser\Node;
use Rector\NodeTraverserQueue\BetterNodeFinder;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverContainerAwareTestCase;
use Rector\NodeTypeResolver\Tests\StandaloneNodeTraverserQueue;
use Symfony\Component\Finder\SplFileInfo;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

abstract class AbstractNodeTypeResolverTest extends AbstractNodeTypeResolverContainerAwareTestCase
{
    /**
     * @var BetterNodeFinder
     */
    protected $betterNodeFinder;

    /**
     * @var NodeTypeResolver
     */
    protected $nodeTypeResolver;

    /**
     * @var StandaloneNodeTraverserQueue
     */
    private $standaloneNodeTraverserQueue;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    protected function setUp(): void
    {
        $this->betterNodeFinder = $this->container->get(BetterNodeFinder::class);
        $this->standaloneNodeTraverserQueue = $this->container->get(StandaloneNodeTraverserQueue::class);
        $this->parameterProvider = $this->container->get(ParameterProvider::class);
        $this->nodeTypeResolver = $this->container->get(NodeTypeResolver::class);
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
    protected function getNodesForFile(string $file): array
    {
        $fileInfo = new SplFileInfo($file, '', '');

        $this->parameterProvider->changeParameter('source', [$file]);

        return $this->standaloneNodeTraverserQueue->processFileInfo($fileInfo);
    }
}
