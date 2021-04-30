<?php
declare(strict_types=1);

namespace Rector\Core\ValueObjectFactory;

use Rector\Core\ValueObject\EditorConfigConfiguration;

final class EditorConfigConfigurationBuilder
{
    /**
     * @var string
     */
    private $indentStyle;

    /**
     * @var int
     */
    private $indentSize;

    /**
     * @var int
     */
    private $tabWidth;

    /**
     * @var string
     */
    private $endOfLine;

    /**
     * @var bool
     */
    private $insertFinalNewline;

    private function __construct()
    {
        $this->indentStyle = EditorConfigConfiguration::SPACE;
        $this->indentSize = 2;
        $this->tabWidth = 1;
        $this->endOfLine = EditorConfigConfiguration::LINE_FEED;
        $this->insertFinalNewline = true;
    }

    public static function anEditorConfigConfiguration(): self
    {
        return new self();
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

    public function withTabWidth(int $tabWidth): self
    {
        $this->tabWidth = $tabWidth;

        return $this;
    }

    public function withEndOfLine(string $endOfLine): self
    {
        $this->endOfLine = $endOfLine;

        return $this;
    }

    public function withFinalNewline(): self
    {
        $this->insertFinalNewline = true;

        return $this;
    }

    public function withInsertFinalNewline(bool $insertFinalNewline): self
    {
        $this->insertFinalNewline = $insertFinalNewline;

        return $this;
    }

    public function withoutFinalNewline(): self
    {
        $this->insertFinalNewline = false;

        return $this;
    }

    public function withLineFeed(): self
    {
        $this->endOfLine = EditorConfigConfiguration::LINE_FEED;

        return $this;
    }

    public function withSpace(): self
    {
        $this->indentStyle = EditorConfigConfiguration::SPACE;

        return $this;
    }

    public function withTab(): self
    {
        $this->indentStyle = EditorConfigConfiguration::TAB;

        return $this;
    }

    public function build(): EditorConfigConfiguration
    {
        return new EditorConfigConfiguration(
            $this->indentStyle,
            $this->indentSize,
            $this->endOfLine,
            $this->tabWidth,
            $this->insertFinalNewline
        );
    }
}
