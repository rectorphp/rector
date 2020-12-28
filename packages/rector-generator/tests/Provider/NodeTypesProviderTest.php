<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Tests\Provider;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\RectorGenerator\Provider\NodeTypesProvider;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class NodeTypesProviderTest extends AbstractKernelTestCase
{
    /**
     * @var NodeTypesProvider
     */
    private $nodeTypesProvider;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->nodeTypesProvider = $this->getService(NodeTypesProvider::class);
    }

    public function test(): void
    {
        $nodeTypes = $this->nodeTypesProvider->provide();
        $nodeTypeCount = count($nodeTypes);
        $this->assertGreaterThan(70, $nodeTypeCount);

        $this->assertContains('Expr\New_', $nodeTypes);
        $this->assertContains('Param', $nodeTypes);
    }
}
