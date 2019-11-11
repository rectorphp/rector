<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Tests\Probe;

use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe;
use stdClass;

final class TypeStaticProbeTest extends TestCase
{
    /**
     * @param mixed $value
     * @dataProvider provideDataForTest()
     */
    public function test($value, string $methodName, int $argumentPosition, string $expectedProbeItem): void
    {
        $probeItem = TypeStaticProbe::createProbeItem($value, $methodName, $argumentPosition);
        $this->assertSame($expectedProbeItem, $probeItem);
    }

    public function provideDataForTest(): Iterator
    {
        yield [5, 'SomeMethod', 0, 'integer;SomeMethod;0' . PHP_EOL];
    }

    /**
     * @param mixed $value
     * @dataProvider provideDataForTestResolveValueTypeToString()
     */
    public function testResolveValueTypeToString($value, string $expectedValueTypeString): void
    {
        $this->assertSame($expectedValueTypeString, TypeStaticProbe::resolveValueTypeToString($value));
    }

    public function provideDataForTestResolveValueTypeToString(): Iterator
    {
        yield [5, 'integer'];
        yield ['hi', 'string'];
        yield [new stdClass(), 'object:stdClass'];
        yield [[new stdClass()], 'array:object:stdClass'];
        yield [[[new stdClass()]], 'array:array:object:stdClass'];
        yield [[5], 'array:integer'];
        yield [[5, 'ou'], 'array:integer|string'];
        yield [[5, ['ou']], 'array:integer|array:string'];
    }
}
