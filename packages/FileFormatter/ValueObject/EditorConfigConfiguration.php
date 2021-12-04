<?php

declare(strict_types=1);

namespace Rector\FileFormatter\ValueObject;

/**
 * @see \Rector\Tests\FileFormatter\ValueObject\EditorConfigConfigurationTest
 */
final class EditorConfigConfiguration
{
    public function __construct(
        private readonly Indent $indent,
        private readonly NewLine $newLine,
        private readonly bool $insertFinalNewline
    ) {
    }

    public function getNewLine(): string
    {
        return $this->newLine->__toString();
    }

    public function getFinalNewline(): string
    {
        return $this->insertFinalNewline ? $this->getNewLine() : '';
    }

    public function getIndent(): string
    {
        return $this->indent->__toString();
    }

    public function getIndentStyleCharacter(): string
    {
        return $this->indent->getIndentStyleCharacter();
    }

    public function getIndentStyle(): string
    {
        return $this->indent->getIndentStyle();
    }

    public function getIndentSize(): int
    {
        return $this->indent->getIndentSize();
    }
}
