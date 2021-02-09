<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\ValueObjectFactory;

use Iterator;
use Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Doctrine\ColumnTagValueNodeFactory;
use Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Symfony\SymfonyRouteTagValueNodeFactory;
use Rector\BetterPhpDocParser\ValueObjectFactory\TagValueNodeConfigurationFactory;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class TagValueNodeConfigurationFactoryTest extends AbstractKernelTestCase
{
    /**
     * @var TagValueNodeConfigurationFactory
     */
    private $tagValueNodeConfigurationFactory;

    /**
     * @var SymfonyRouteTagValueNodeFactory
     */
    private $symfonyRouteTagValueNodeFactory;

    /**
     * @var ColumnTagValueNodeFactory
     */
    private $columnTagValueNodeFactory;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->tagValueNodeConfigurationFactory = $this->getService(TagValueNodeConfigurationFactory::class);
        $this->symfonyRouteTagValueNodeFactory = $this->getService(SymfonyRouteTagValueNodeFactory::class);
        $this->columnTagValueNodeFactory = $this->getService(ColumnTagValueNodeFactory::class);
    }

    public function test(): void
    {
        $symfonyRouteTagValueNode = $this->symfonyRouteTagValueNodeFactory->create();

        $tagValueNodeConfiguration = $this->tagValueNodeConfigurationFactory->createFromOriginalContent(
            '...',
            $symfonyRouteTagValueNode
        );

        $this->assertSame('=', $tagValueNodeConfiguration->getArrayEqualSign());
    }

    /**
     * @dataProvider provideData()
     */
    public function testArrayColonIsNotChangedToEqual(string $originalContent): void
    {
        $tagValueNodeConfiguration = $this->tagValueNodeConfigurationFactory->createFromOriginalContent(
            $originalContent,
            $this->columnTagValueNodeFactory->create()
        );

        $this->assertSame(':', $tagValueNodeConfiguration->getArrayEqualSign());
    }

    public function provideData(): Iterator
    {
        yield ['(type="integer", nullable=true, options={"default":0})'];
    }
}
