<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\TestClassResolver;

use Iterator;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector;
use Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\DowngradeTypedPropertyRectorTest;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\TypedPropertyRectorTest;
use Rector\PHPUnit\TestClassResolver\TestClassResolver;
use Rector\PHPUnit\Tests\TestClassResolver\Source\SeeSomeClass;
use Rector\PHPUnit\Tests\TestClassResolver\Source\SeeSomeClassTest;
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
