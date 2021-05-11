<?php

declare(strict_types=1);

namespace Rector\Core\Tests\PhpParser\Node\BetterNodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Parser\SimplePhpParser;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class BetterNodeFinderTest extends AbstractTestCase
{
    /**
     * @var Node[]
     */
    private array $nodes = [];

    private BetterNodeFinder $betterNodeFinder;

    protected function setUp(): void
    {
        $this->boot();

        $this->betterNodeFinder = $this->getService(BetterNodeFinder::class);

        /** @var SimplePhpParser $simplePhpParser */
        $simplePhpParser = $this->getService(SimplePhpParser::class);
        $this->nodes = $simplePhpParser->parseFile(__DIR__ . '/Source/SomeFile.php.inc');
    }

    public function testFindFirstAncestorInstanceOf(): void
    {
        $variable = $this->betterNodeFinder->findFirstInstanceOf($this->nodes, Variable::class);
        $class = $this->betterNodeFinder->findFirstInstanceOf($this->nodes, Class_::class);

        $this->assertNotNull($variable);
        $this->assertNotNull($class);

        $this->assertInstanceOf(Variable::class, $variable);
        $this->assertInstanceOf(Class_::class, $class);

        /** @var Variable $variable */
        $classLikeNode = $this->betterNodeFinder->findParentType($variable, ClassLike::class);
        $this->assertSame($classLikeNode, $class);
    }

    public function testFindMissingFirstAncestorInstanceOf(): void
    {
        /** @var Variable $variableNode */
        $variableNode = $this->betterNodeFinder->findFirstInstanceOf($this->nodes, Variable::class);

        $this->assertNull($this->betterNodeFinder->findParentType($variableNode, Array_::class));
    }
}
