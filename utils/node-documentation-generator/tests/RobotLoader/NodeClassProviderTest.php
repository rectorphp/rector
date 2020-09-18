<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\Tests\RobotLoader;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\Utils\NodeDocumentationGenerator\RobotLoader\NodeClassProvider;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class NodeClassProviderTest extends AbstractKernelTestCase
{
    /**
     * @var NodeClassProvider
     */
    private $nodeClassProvider;

    protected function setUp(): void
    {
        self::bootKernel(RectorKernel::class);
        $this->nodeClassProvider = self::$container->get(NodeClassProvider::class);
    }

    public function test(): void
    {
        $nodeClasses = $this->nodeClassProvider->getNodeClasses();
        $this->assertGreaterThan(100, $nodeClasses);
    }
}
