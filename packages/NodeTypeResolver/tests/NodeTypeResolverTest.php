<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests;

use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Tests\AbstractContainerAwareTestCase;

final class NodeTypeResolverTest extends AbstractContainerAwareTestCase
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    protected function setUp(): void
    {
        $this->nodeTypeResolver = $this->container->get(NodeTypeResolver::class);
    }

//    public function test()
//    {
//        $this->nodeTypeResolver->resolve();
//    }
}
