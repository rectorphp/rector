<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\ValueObjectFactory;

use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\ValueObjectFactory\TagValueNodeConfigurationFactory;

final class TagValueNodeConfigurationFactoryTest extends TestCase
{
    /**
     * @var TagValueNodeConfigurationFactory
     */
    private $tagValueNodeConfigurationFactory;

    protected function setUp(): void
    {
        $this->tagValueNodeConfigurationFactory = new TagValueNodeConfigurationFactory();
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
