<?php

declare (strict_types=1);
namespace RectorPrefix20210511\Symplify\Astral\Tests\Naming;

use Iterator;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use RectorPrefix20210511\Symplify\Astral\HttpKernel\AstralKernel;
use RectorPrefix20210511\Symplify\Astral\Naming\SimpleNameResolver;
use RectorPrefix20210511\Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
final class SimpleNameResolverTest extends \RectorPrefix20210511\Symplify\PackageBuilder\Testing\AbstractKernelTestCase
{
    /**
     * @var SimpleNameResolver
     */
    private $simpleNameResolver;
    protected function setUp() : void
    {
        $this->bootKernel(\RectorPrefix20210511\Symplify\Astral\HttpKernel\AstralKernel::class);
        $this->simpleNameResolver = $this->getService(\RectorPrefix20210511\Symplify\Astral\Naming\SimpleNameResolver::class);
    }
    /**
     * @dataProvider provideData()
     */
    public function test(\PhpParser\Node $node, string $expectedName) : void
    {
        $resolvedName = $this->simpleNameResolver->getName($node);
        $this->assertSame($expectedName, $resolvedName);
    }
    /**
     * @return Iterator<string[]|Identifier[]>
     */
    public function provideData() : \Iterator
    {
        $identifier = new \PhpParser\Node\Identifier('first name');
        (yield [$identifier, 'first name']);
    }
}
