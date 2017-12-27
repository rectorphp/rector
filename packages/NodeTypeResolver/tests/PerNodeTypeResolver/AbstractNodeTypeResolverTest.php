<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver;

use PhpParser\Node;
use Rector\NodeTraverserQueue\BetterNodeFinder;
use Rector\NodeTraverserQueue\NodeTraverserQueue;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Tests\AbstractContainerAwareTestCase;
use SplFileInfo;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

abstract class AbstractNodeTypeResolverTest extends AbstractContainerAwareTestCase
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
     * @var NodeTraverserQueue
     */
    private $nodeTraverserQueue;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    protected function setUp(): void
    {
        $this->betterNodeFinder = $this->container->get(BetterNodeFinder::class);
        $this->nodeTraverserQueue = $this->container->get(NodeTraverserQueue::class);
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
        $fileInfo = new SplFileInfo($file);

        $this->parameterProvider->changeParameter('source', [$file]);

        [$newStmts,] = $this->nodeTraverserQueue->processFileInfo($fileInfo);

        return $newStmts;
    }
}
