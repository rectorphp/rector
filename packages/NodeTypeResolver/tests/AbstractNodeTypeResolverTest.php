<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests;

use PhpParser\Node;
use Rector\NodeTraverserQueue\BetterNodeFinder;
use Rector\NodeTraverserQueue\NodeTraverserQueue;
use Rector\Tests\AbstractContainerAwareTestCase;
use SplFileInfo;

abstract class AbstractNodeTypeResolverTest extends AbstractContainerAwareTestCase
{
    /**
     * @var BetterNodeFinder
     */
    protected $betterNodeFinder;

    /**
     * @var NodeTraverserQueue
     */
    private $nodeTraverserQueue;

    protected function setUp(): void
    {
        $this->betterNodeFinder = $this->container->get(BetterNodeFinder::class);
        $this->nodeTraverserQueue = $this->container->get(NodeTraverserQueue::class);
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

        [$newStmts,] = $this->nodeTraverserQueue->processFileInfo($fileInfo);

        return $newStmts;
    }

    protected function doTestAttributeEquals(Node $node, string $attribute, array $expectedContent): void
    {
        $this->assertSame($expectedContent, $node->getAttribute($attribute));
    }
}
