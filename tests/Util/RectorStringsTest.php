<?php declare(strict_types=1);

namespace Rector\Tests\Util;

use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\Util\RectorStrings;

final class RectorStringsTest extends TestCase
{
    /**
     * @dataProvider provideDataForCamelCaseToUnderscore()
     */
    public function testCamelCaseToUnderscore(string $content, string $expected): void
    {
        $this->assertSame($expected, RectorStrings::camelCaseToUnderscore($content));
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
}
