<?php

declare(strict_types=1);

namespace Rector\Tests\PHPUnit\TestClassResolver;

use Iterator;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector;
use Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\DowngradeTypedPropertyRectorTest;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\TypedPropertyRectorTest;
use Rector\PHPUnit\TestClassResolver\TestClassResolver;
use Rector\Tests\PHPUnit\TestClassResolver\Source\SeeSomeClass;
use Rector\Tests\PHPUnit\TestClassResolver\Source\SeeSomeClassTest;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class TestClassResolverTest extends AbstractKernelTestCase
{
    /**
     * @var TestClassResolver
     */
    private $testClassResolver;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->testClassResolver = $this->getService(TestClassResolver::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(string $rectorClass, string $expectedTestClass): void
    {
        $testClass = $this->testClassResolver->resolveFromClassName($rectorClass);
        $this->assertSame($expectedTestClass, $testClass);
    }

    public function provideData(): Iterator
    {
        yield [SeeSomeClass::class, SeeSomeClassTest::class];
        yield [TypedPropertyRector::class, TypedPropertyRectorTest::class];
        yield [DowngradeTypedPropertyRector::class, DowngradeTypedPropertyRectorTest::class];
    }
}
