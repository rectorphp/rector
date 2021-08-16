<?php

declare(strict_types=1);

namespace Rector\Tests\FileFormatter\ValueObject;

use Iterator;
use Rector\FileFormatter\Exception\InvalidNewLineStringException;
use Rector\FileFormatter\ValueObject\NewLine;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NewLineTest extends AbstractTestCase
{
    /**
     * @dataProvider extractFromFiles
     */
    public function testFromFiles(SmartFileInfo $smartFileInfo, string $expectedNewLine): void
    {
        $newLine = NewLine::fromContent($smartFileInfo->getContents());
        $this->assertSame($expectedNewLine, $newLine->__toString());
    }

    /**
     * @dataProvider provideInvalidNewLineString
     */
    public function testFromStringRejectsInvalidNewLineString(string $string): void
    {
        $this->expectException(InvalidNewLineStringException::class);

        NewLine::fromSingleCharacter($string);
    }

    /**
     * @dataProvider provideValidNewLineString
     */
    public function testFromStringReturnsNewLine(string $string): void
    {
        $newLine = NewLine::fromSingleCharacter($string);

        $this->assertSame($string, $newLine->__toString());
    }

    /**
     * @dataProvider provideValidNewLineStringFromEditorConfig
     */
    public function testFromEditorConfigReturnsNewLine(string $string, string $expected): void
    {
        $newLine = NewLine::fromEditorConfig($string);

        $this->assertSame($expected, $newLine->__toString());
    }

    /**
     * @return Iterator<array<string>>
     */
    public function provideValidNewLineString(): Iterator
    {
        foreach (["\n", "\r", "\r\n"] as $string) {
            yield [$string];
        }
    }

    /**
     * @return Iterator<array<string>>
     */
    public function provideInvalidNewLineString(): Iterator
    {
        foreach (["\t", " \r ", " \r\n ", " \n ", ' ', "\f", "\x0b", "\x85"] as $string) {
            yield [$string];
        }
    }

    /**
     * @return Iterator<array<string>>
     */
    public function extractFromFiles(): Iterator
    {
        yield 'Yaml file with carriage return' => [
            new SmartFileInfo(__DIR__ . '/Fixture/yaml_carriage_return.yaml'),
            "\r",
        ];

        yield 'Xml file with line feed' => [new SmartFileInfo(__DIR__ . '/Fixture/xml_line_feed.xml'), "\n"];

        yield 'Json file with line feed' => [new SmartFileInfo(__DIR__ . '/Fixture/composer_line_feed.json'), "\n"];

        yield 'Json file with carriage return' => [
            new SmartFileInfo(__DIR__ . '/Fixture/composer_carriage_return.json'),
            "\r",
        ];

        yield 'Json file with carriage return and line feed' => [
            new SmartFileInfo(__DIR__ . '/Fixture/composer_carriage_return_line_feed.json'),
            "\r\n",
        ];
    }

    /**
     * @return Iterator<array<string>>
     */
    public function provideValidNewLineStringFromEditorConfig(): Iterator
    {
        foreach ([
            'lf' => "\n",
            'cr' => "\r",
            'crlf' => "\r\n",
        ] as $editorConfig => $string) {
            yield [$editorConfig, $string];
        }
    }
}
