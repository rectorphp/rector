<?php declare(strict_types=1);

namespace Rector\NodeTraverserQueue\Tests;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

final class BetterNodeFinderTest extends AbstractNodeTypeResolverTest
{
    /**
     * @var Node[]
     */
    private $nodes = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->nodes = $this->getNodesForFile(__DIR__ . '/BetterNodeFinderSource/SomeFile.php.inc');
    }

    public function testFindFirstAncestorInstanceOf(): void
    {
        /** @var Variable $variableNode */
        $variableNode = $this->betterNodeFinder->findFirstInstanceOf($this->nodes, Variable::class);
        $classNode = $this->betterNodeFinder->findFirstInstanceOf($this->nodes, Class_::class);

        $classLikeNode = $this->betterNodeFinder->findFirstAncestorInstanceOf($variableNode, ClassLike::class);
        $this->assertSame($classLikeNode, $classNode);
    }

    public function testFindMissingFirstAncestorInstanceOf(): void
    {
        /** @var Variable $variableNode */
        $variableNode = $this->betterNodeFinder->findFirstInstanceOf($this->nodes, Variable::class);

        $this->assertNull($this->betterNodeFinder->findFirstAncestorInstanceOf($variableNode, Array_::class));
    }
}
