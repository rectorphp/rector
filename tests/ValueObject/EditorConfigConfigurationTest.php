<?php

namespace Rector\Core\Tests\ValueObject;

use PHPUnit\Framework\TestCase;
use Rector\Core\Exception\EditorConfigConfigurationException;
use Rector\Core\ValueObject\EditorConfigConfiguration;
use Rector\Core\ValueObjectFactory\EditorConfigConfigurationBuilder;
use Symplify\PackageBuilder\Configuration\StaticEolConfiguration;

final class EditorConfigConfigurationTest extends TestCase
{
    /**
     * @dataProvider invalidIndentStyle
     */
    public function testInvalidIndentStyleThrowsException(string $indentStyle): void
    {
        $this->expectException(EditorConfigConfigurationException::class);
        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withIndentStyle($indentStyle);
        $editorConfigConfigurationBuilder->build();
    }

    /**
     * @dataProvider validIndentStyle
     */
    public function testValidIndentStyle(string $indentStyle): void
    {
        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withIndentStyle($indentStyle);
        $editorConfigConfiguration = $editorConfigConfigurationBuilder->build();

        self::assertSame($editorConfigConfiguration->getIndentStyle(), $indentStyle);
    }

    /**
     * @dataProvider validEndOfLine
     */
    public function testValidEndOfLine(string $endOfLine, string $expectedEndOfLine): void
    {
        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withEndOfLine($endOfLine);
        $editorConfigConfiguration = $editorConfigConfigurationBuilder->build();

        self::assertSame($editorConfigConfiguration->getEndOfLine(), $expectedEndOfLine);
    }

    public function testWithFinalNewline(): void
    {
        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withFinalNewline();
        $editorConfigConfiguration = $editorConfigConfigurationBuilder->build();

        self::assertSame(StaticEolConfiguration::getEolChar(), $editorConfigConfiguration->getFinalNewline());
    }

    public function testWithoutFinalNewline(): void
    {
        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withoutFinalNewline();
        $editorConfigConfiguration = $editorConfigConfigurationBuilder->build();

        self::assertSame('', $editorConfigConfiguration->getFinalNewline());
    }

    public function testIndentForTab(): void
    {
        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withTab();
        $editorConfigConfigurationBuilder->withTabWidth(4);
        $editorConfigConfiguration = $editorConfigConfigurationBuilder->build();

        self::assertSame('				', $editorConfigConfiguration->getIndent());
    }

    public function testIndentForSpace(): void
    {
        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withSpace();
        $editorConfigConfigurationBuilder->withIndentSize(10);
        $editorConfigConfiguration = $editorConfigConfigurationBuilder->build();

        self::assertSame('          ', $editorConfigConfiguration->getIndent());
    }

    /**
     * @dataProvider inValidEndOfLine
     */
    public function testInValidEndOfLine(string $endOfLine): void
    {
        $this->expectException(EditorConfigConfigurationException::class);

        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withEndOfLine($endOfLine);
        $editorConfigConfigurationBuilder->build();
    }

    /**
     * @return array<string, string[]>
     */
    public function invalidIndentStyle(): array
    {
        return [
            'foo' => ['foo'],
            'bar' => ['bar'],
            'baz' => ['baz'],
        ];
    }

    /**
     * @return array<string, string[]>
     */
    public function validIndentStyle(): array
    {
        return [
            'Tab' => [EditorConfigConfiguration::TAB],
            'Space' => [EditorConfigConfiguration::SPACE],
        ];
    }

    /**
     * @return array<string, string[]>
     */
    public function validEndOfLine(): array
    {
        return [
            'Line feed' => [EditorConfigConfiguration::LINE_FEED, "\n"],
            'Carriage return' => [EditorConfigConfiguration::CARRIAGE_RETURN, "\r"],
            'Carriage return and line feed' => [EditorConfigConfiguration::CARRIAGE_RETURN_LINE_FEED, "\r\n"],
        ];
    }

    /**
     * @return array<string, string[]>
     */
    public function inValidEndOfLine(): array
    {
        return [
            'foo' => ['foo'],
            'bar' => ['bar'],
            'baz' => ['baz'],
        ];
    }
}
