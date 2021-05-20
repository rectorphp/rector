<?php

namespace Rector\Tests\FileFormatter\ValueObject;

use Generator;
use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\FileFormatter\Exception\InvalidIndentSizeException;
use Rector\FileFormatter\Exception\InvalidIndentStyleException;
use Rector\FileFormatter\Exception\ParseIndentException;
use Rector\FileFormatter\ValueObject\Indent;
use Symplify\SmartFileSystem\SmartFileInfo;

final class IndentTest extends TestCase
{
    /**
     * @dataProvider extractFromFiles
     */
    public function testFromFiles(SmartFileInfo $smartFileInfo, string $expectedIndent): void
    {
        $indent = Indent::fromContent($smartFileInfo->getContents());
        $this->assertSame($expectedIndent, $indent->__toString());
    }

    /**
     * @dataProvider provideSizeStyleAndIndentString
     */
    public function testFromSizeAndStyle(int $size, string $style, string $string): void
    {
        $indent = Indent::fromSizeAndStyle($size, $style);

        $this->assertSame($string, $indent->__toString());
        $this->assertSame($size, $indent->getIndentSize());
        $this->assertSame($style, $indent->getIndentStyle());
    }

    public function testFromSizeAndStyleWithInvalidSizeThrowsException(): void
    {
        $this->expectException(InvalidIndentSizeException::class);
        Indent::fromSizeAndStyle(0, 'invalid');
    }

    public function testFromSizeAndStyleWithInvalidStyleThrowsException(): void
    {
        $this->expectException(InvalidIndentStyleException::class);
        Indent::fromSizeAndStyle(1, 'invalid');
    }

    public function testFromInvalidContentThrowsException(): void
    {
        $this->expectException(ParseIndentException::class);
        Indent::fromContent('This is invalid content');
    }

    /**
     * @return Iterator<array<string>>
     */
    public function extractFromFiles(): Iterator
    {
        yield 'Yaml file with space indentation of size 4' => [
            new SmartFileInfo(__DIR__ . '/Fixture/yaml_indentation_space_four.yaml'),
            '    ',
        ];

        yield 'Yaml file with space indentation of size 2' => [
            new SmartFileInfo(__DIR__ . '/Fixture/yaml_indentation_space_two.yaml'),
            '  ',
        ];

        yield 'Json file with tab indentation of size 2' => [
            new SmartFileInfo(__DIR__ . '/Fixture/composer_indentation_tab_two.json'),
            '		',
        ];

        yield 'Json file with space indentation of size 6' => [
            new SmartFileInfo(__DIR__ . '/Fixture/composer_indentation_space_six.json'),
            '      ',
        ];
    }

    /**
     * @return Generator<array{0: int, 1: string, 2: string}>
     */
    public function provideSizeStyleAndIndentString(): Iterator
    {
        foreach ($this->sizes() as $size) {
            foreach (Indent::CHARACTERS as $style => $character) {
                $string = str_repeat($character, $size);

                yield [$size, $style, $string];
            }
        }
    }

    /**
     * @return int[]
     */
    private static function sizes(): array
    {
        return [
            'int-one' => 1,
            'int-greater-than-one' => 5,
        ];
    }
}
