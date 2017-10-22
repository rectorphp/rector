<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use Rector\Node\Attribute;
use Rector\NodeTraverserQueue\NodeTraverserQueue;
use Rector\Tests\AbstractContainerAwareTestCase;
use SplFileInfo;

final class NodeTypeResolverTest extends AbstractContainerAwareTestCase
{
    /**
     * @var NodeTraverserQueue
     */
    private $nodeTraverserQueue;

    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    protected function setUp(): void
    {
        $this->nodeTraverserQueue = $this->container->get(NodeTraverserQueue::class);
        $this->nodeFinder = $this->container->get(NodeFinder::class);
    }

    public function testVariables(): void
    {
        $nodes = $this->getNodesWithTypesForFile(__DIR__ . '/Source/SomeClass.php.inc');
        $variableNodes = $this->nodeFinder->findInstanceOf($nodes, Variable::class);

        $this->assertSame('SomeNamespace\AnotherType', $variableNodes[0]->getAttribute(Attribute::TYPE));
        $this->assertSame('SomeNamespace\AnotherType', $variableNodes[1]->getAttribute(Attribute::TYPE));
        $this->assertSame('SomeNamespace\AnotherType', $variableNodes[2]->getAttribute(Attribute::TYPE));
    }

    public function testProperties(): void
    {
        $nodes = $this->getNodesWithTypesForFile(__DIR__ . '/Source/SomeClass.php.inc');
        $propertyNodes = $this->nodeFinder->findInstanceOf($nodes, Property::class);

        $this->assertSame('SomeNamespace\PropertyType', $propertyNodes[0]->getAttribute(Attribute::TYPE));
    }

    /**
     * @return Node[]
     */
    private function getNodesWithTypesForFile(string $file): array
    {
        $fileInfo = new SplFileInfo($file);

        [$newStmts,] = $this->nodeTraverserQueue->processFileInfo($fileInfo);

        return $newStmts;
    }
}
