<?php

declare (strict_types=1);
namespace RectorPrefix20210511\Symplify\Astral\Tests\NodeValue;

use Iterator;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeFinder;
use RectorPrefix20210511\PHPUnit\Framework\TestCase;
use RectorPrefix20210511\Symplify\Astral\NodeFinder\SimpleNodeFinder;
use RectorPrefix20210511\Symplify\Astral\NodeValue\NodeValueResolver;
use RectorPrefix20210511\Symplify\Astral\StaticFactory\SimpleNameResolverStaticFactory;
use RectorPrefix20210511\Symplify\PackageBuilder\Php\TypeChecker;
final class NodeValueResolverTest extends \RectorPrefix20210511\PHPUnit\Framework\TestCase
{
    /**
     * @var NodeValueResolver
     */
    private $nodeValueResolver;
    protected function setUp() : void
    {
        $simpleNameResolver = \RectorPrefix20210511\Symplify\Astral\StaticFactory\SimpleNameResolverStaticFactory::create();
        $simpleNodeFinder = new \RectorPrefix20210511\Symplify\Astral\NodeFinder\SimpleNodeFinder(new \RectorPrefix20210511\Symplify\PackageBuilder\Php\TypeChecker(), new \PhpParser\NodeFinder());
        $this->nodeValueResolver = new \RectorPrefix20210511\Symplify\Astral\NodeValue\NodeValueResolver($simpleNameResolver, new \RectorPrefix20210511\Symplify\PackageBuilder\Php\TypeChecker(), $simpleNodeFinder);
    }
    /**
     * @dataProvider provideData()
     */
    public function test(\PhpParser\Node\Expr $expr, string $expectedValue) : void
    {
        $resolvedValue = $this->nodeValueResolver->resolve($expr, __FILE__);
        $this->assertSame($expectedValue, $resolvedValue);
    }
    /**
     * @return Iterator<string[]|String_[]>
     */
    public function provideData() : \Iterator
    {
        (yield [new \PhpParser\Node\Scalar\String_('value'), 'value']);
    }
}
