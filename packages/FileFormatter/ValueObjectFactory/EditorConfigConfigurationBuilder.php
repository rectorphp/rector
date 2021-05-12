<?php

declare(strict_types=1);

namespace Rector\FileFormatter\ValueObjectFactory;

use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObject\NewLine;

final class EditorConfigConfigurationBuilder
{
    private NewLine $newLine;

    public function __construct(
        private string $indentStyle = 'space',
        private int $indentSize = 2,
        private bool $insertFinalNewline = true
    ) {
        $this->newLine = NewLine::fromEditorConfig('lf');
    }

    public static function create(): self
    {
        return new self();
    }

    public function withNewLine(NewLine $newLine): self
    {
        $this->newLine = $newLine;

        return $this;
    }

    public function withIndent(Indent $indent): self
    {
        $this->indentSize = $indent->getIndentSize();
        $this->indentStyle = $indent->getIndentStyle();

        return $this;
    }

    public function withIndentStyle(string $indentStyle): self
    {
        $this->indentStyle = $indentStyle;

        return $this;
    }

    public function withIndentSize(int $indentSize): self
    {
        $this->indentSize = $indentSize;

        return $this;
    }

    public function withInsertFinalNewline(bool $insertFinalNewline): self
    {
        $this->insertFinalNewline = $insertFinalNewline;

        return $this;
    }

    public function withEndOfLineFromEditorConfig(string $endOfLine): self
    {
        $this->newLine = NewLine::fromEditorConfig($endOfLine);

        return $this;
    }

    public function build(): EditorConfigConfiguration
    {
        $newLine = $this->newLine;

        return new EditorConfigConfiguration(
            Indent::fromSizeAndStyle($this->indentSize, $this->indentStyle),
            $newLine,
            $this->insertFinalNewline
        );
    }
}
