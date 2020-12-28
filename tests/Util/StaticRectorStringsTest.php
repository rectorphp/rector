<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Util;

use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\Core\Util\StaticRectorStrings;

final class StaticRectorStringsTest extends TestCase
{
    /**
     * @dataProvider provideDataForDashesToCamelCase()
     */
    public function testDashesToCamelCase(string $content, string $expected): void
    {
        $this->assertSame($expected, StaticRectorStrings::dashesToCamelCase($content));
    }

    public function provideDataForDashesToCamelCase(): Iterator
    {
        yield ['simple-test', 'SimpleTest'];
        yield ['easy', 'Easy'];
    }

    /**
     * @dataProvider provideDataForCamelCaseToUnderscore()
     */
    public function testCamelCaseToUnderscore(string $content, string $expected): void
    {
        $this->assertSame($expected, StaticRectorStrings::camelCaseToUnderscore($content));
    }

    public function provideDataForCamelCaseToUnderscore(): Iterator
    {
        yield ['simpleTest', 'simple_test'];
        yield ['easy', 'easy'];
        yield ['HTML', 'html'];
        yield ['simpleXML', 'simple_xml'];
        yield ['PDFLoad', 'pdf_load'];
        yield ['startMIDDLELast', 'start_middle_last'];
        yield ['AString', 'a_string'];
        yield ['Some4Numbers234', 'some4_numbers234'];
        yield ['TEST123String', 'test123_string'];
    }

    /**
     * @dataProvider provideDataForUnderscoreToCamelCase()
     */
    public function testUnderscoreToCamelCase(string $content, string $expected): void
    {
        $this->assertSame($expected, StaticRectorStrings::underscoreToCamelCase($content));
    }

    public function provideDataForUnderscoreToCamelCase(): Iterator
    {
        yield ['simple_test', 'simpleTest'];
    }

    /**
     * @dataProvider provideDataForUnderscoreToPascalCase()
     */
    public function testUnderscoreToPascalCase(string $content, string $expected): void
    {
        $this->assertSame($expected, StaticRectorStrings::underscoreToPascalCase($content));
    }

    public function provideDataForUnderscoreToPascalCase(): Iterator
    {
        yield ['simple_test', 'SimpleTest'];
    }
}
