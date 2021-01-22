<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\ValueObjectFactory;

use Iterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\ValueObjectFactory\TagValueNodeConfigurationFactory;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class TagValueNodeConfigurationFactoryTest extends AbstractKernelTestCase
{
    /**
     * @var TagValueNodeConfigurationFactory
     */
    private $tagValueNodeConfigurationFactory;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->tagValueNodeConfigurationFactory = $this->getService(TagValueNodeConfigurationFactory::class);
    }

    public function test(): void
    {
        $tagValueNodeConfiguration = $this->tagValueNodeConfigurationFactory->createFromOriginalContent(
            '...',
            new SymfonyRouteTagValueNode([])
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
            new ColumnTagValueNode([])
        );

        $this->assertSame(':', $tagValueNodeConfiguration->getArrayEqualSign());
    }

    public function provideData(): Iterator
    {
        yield ['(type="integer", nullable=true, options={"default":0})'];
    }
}
