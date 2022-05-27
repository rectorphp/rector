<?php

declare (strict_types=1);
namespace Rector\FileFormatter\ValueObjectFactory;

use Rector\FileFormatter\Enum\IndentType;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObject\NewLine;
final class EditorConfigConfigurationBuilder
{
    /**
     * @var \Rector\FileFormatter\ValueObject\NewLine
     */
    private $newLine;
    /**
     * @var IndentType::*
     */
    private $indentStyle = IndentType::SPACE;
    /**
     * @var int
     */
    private $indentSize = 2;
    /**
     * @var bool
     */
    private $insertFinalNewline = \true;
    /**
     * @param IndentType::* $indentStyle
     */
    public function __construct(string $indentStyle = IndentType::SPACE, int $indentSize = 2, bool $insertFinalNewline = \true)
    {
        $this->indentStyle = $indentStyle;
        $this->indentSize = $indentSize;
        $this->insertFinalNewline = $insertFinalNewline;
        $this->newLine = NewLine::fromEditorConfig('lf');
    }
    public static function create() : self
    {
        return new self();
    }
    public function withNewLine(NewLine $newLine) : self
    {
        $this->newLine = $newLine;
        return $this;
    }
    public function withIndent(Indent $indent) : self
    {
        $this->indentSize = $indent->getIndentSize();
        $this->indentStyle = $indent->getIndentStyle();
        return $this;
    }
    /**
     * @param IndentType::* $indentStyle
     */
    public function withIndentStyle(string $indentStyle) : self
    {
        $this->indentStyle = $indentStyle;
        return $this;
    }
    public function withIndentSize(int $indentSize) : self
    {
        $this->indentSize = $indentSize;
        return $this;
    }
    public function withInsertFinalNewline(bool $insertFinalNewline) : self
    {
        $this->insertFinalNewline = $insertFinalNewline;
        return $this;
    }
    public function withEndOfLineFromEditorConfig(string $endOfLine) : self
    {
        $this->newLine = NewLine::fromEditorConfig($endOfLine);
        return $this;
    }
    public function build() : EditorConfigConfiguration
    {
        $newLine = $this->newLine;
        return new EditorConfigConfiguration(Indent::fromSizeAndStyle($this->indentSize, $this->indentStyle), $newLine, $this->insertFinalNewline);
    }
}
