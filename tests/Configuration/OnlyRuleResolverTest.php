<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Configuration;

use Iterator;
use Rector\Core\Configuration\OnlyRuleResolver;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\Tests\Configuration\Source\CustomLocalRector;
use Rector\Php72\Rector\Assign\ListEachRector;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class OnlyRuleResolverTest extends AbstractKernelTestCase
{
    /**
     * @var OnlyRuleResolver
     */
    private $onlyRuleResolver;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [__DIR__ . '/Source/config/some_config.yaml']);
        $this->onlyRuleResolver = self::$container->get(OnlyRuleResolver::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(string $rule, string $expectedRectorClass): void
    {
        $resolveRectorClass = $this->onlyRuleResolver->resolve($rule);
        $this->assertSame($expectedRectorClass, $resolveRectorClass);
    }

    public function provideData(): Iterator
    {
        yield [ListEachRector::class, ListEachRector::class];
        yield ['ListEachRector', ListEachRector::class];
        yield [CustomLocalRector::class, CustomLocalRector::class];
        yield ['CustomLocalRector', CustomLocalRector::class];

        // miss-formats
        yield ['\\' . ListEachRector::class, ListEachRector::class];
        yield ['RectorCoreTestsConfigurationSourceCustomLocalRector', CustomLocalRector::class];
    }
}
