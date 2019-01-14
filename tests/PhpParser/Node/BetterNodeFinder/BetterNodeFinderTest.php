<?php declare(strict_types=1);

namespace Rector\Tests\PhpParser\Node\BetterNodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use Rector\NodeTypeResolver\NodeVisitor\ParentAndNextNodeVisitor;
use Rector\PhpParser\Node\BetterNodeFinder;
use function Safe\file_get_contents;

final class BetterNodeFinderTest extends TestCase
{
    /**
     * @var Node[]
     */
    private $nodes = [];

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    protected function setUp(): void
    {
        $this->betterNodeFinder = new BetterNodeFinder(new NodeFinder());
        $this->nodes = $this->createNodesFromFile(__DIR__ . '/Source/SomeFile.php.inc');
    }

    public function testFindFirstAncestorInstanceOf(): void
    {
        /** @var Variable $variableNode */
        $variableNode = $this->betterNodeFinder->findFirstInstanceOf($this->nodes, Variable::class);
        $classNode = $this->betterNodeFinder->findFirstInstanceOf($this->nodes, Class_::class);

        $this->assertInstanceOf(Variable::class, $variableNode);
        $this->assertInstanceOf(Class_::class, $classNode);

        $classLikeNode = $this->betterNodeFinder->findFirstAncestorInstanceOf($variableNode, ClassLike::class);
        $this->assertSame($classLikeNode, $classNode);
    }

    public function testFindMissingFirstAncestorInstanceOf(): void
    {
        /** @var Variable $variableNode */
        $variableNode = $this->betterNodeFinder->findFirstInstanceOf($this->nodes, Variable::class);

        $this->assertNull($this->betterNodeFinder->findFirstAncestorInstanceOf($variableNode, Array_::class));
    }

    /**
     * @return Node[]
     */
    private function createNodesFromFile(string $filePath): array
    {
        $phpParser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $nodes = $phpParser->parse(file_get_contents($filePath));
        if ($nodes === null) {
            return [];
        }

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new ParentAndNextNodeVisitor());

        return $nodeTraverser->traverse($nodes);
    }
}
