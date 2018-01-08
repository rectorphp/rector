<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\Tests;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\NodeValueResolver\NodeValueResolver;
use Rector\Tests\AbstractContainerAwareTestCase;

final class NodeValueResolverTest extends AbstractContainerAwareTestCase
{
    /**
     * @var NodeValueResolver
     */
    private $nodeValueResolver;

    protected function setUp(): void
    {
        $this->nodeValueResolver = $this->container->get(NodeValueResolver::class);
    }

    /**
     * @dataProvider provideNodeToValueData()
     * @param mixed $expectedValue
     */
    public function test(Node $node, $expectedValue): void
    {
        $this->assertSame($expectedValue, $this->nodeValueResolver->resolve($node));
    }

    /**
     * @return mixed[][]
     */
    public function provideNodeToValueData(): array
    {
        return [
            # string resolver
            [new String_('hi'), 'hi'],
            # const fetch resolver
            [new ConstFetch(new Name('true')), true],
            [new ConstFetch(new Name('FALSE')), false],
            # array value resolver
            [new Array_([new ArrayItem(new ConstFetch(new Name('null')))]), [null]],
        ];
    }
}
