<?php

namespace Rector\Tests\FileFormatter\ValueObject;

use Generator;
use PHPUnit\Framework\TestCase;
use Rector\FileFormatter\Exception\InvalidNewLineStringException;
use Rector\FileFormatter\ValueObject\NewLine;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NewLineTest extends TestCase
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
     * @return Generator<array<string>>
     */
    public function provideValidNewLineString(): Generator
    {
        foreach (["\n", "\r", "\r\n"] as $string) {
            yield [$string];
        }
    }

    /**
     * @return Generator<array<string>>
     */
    public function provideInvalidNewLineString(): Generator
    {
        foreach (["\t", " \r ", " \r\n ", " \n ", ' ', "\f", "\x0b", "\x85"] as $string) {
            yield [$string];
        }
    }

    /**
     * @return Generator<array<string>>
     */
    public function extractFromFiles(): Generator
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
     * @return Generator<array<string>>
     */
    public function provideValidNewLineStringFromEditorConfig(): Generator
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
