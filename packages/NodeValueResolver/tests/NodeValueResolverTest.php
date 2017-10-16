<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\Tests;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
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

    public function testArrayNode(): void
    {
        $arrayNode = new Array_([
            new ArrayItem(new String_('hi')),
            new ArrayItem(BuilderHelpers::normalizeValue(true))
        ]);


        $resolved = $this->nodeValueResolver->resolve($arrayNode);
        $this->assertSame(['hi', true], $resolved);
    }
}
