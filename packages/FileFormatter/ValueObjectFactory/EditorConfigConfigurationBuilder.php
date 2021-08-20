<?php

declare (strict_types=1);
namespace Rector\FileFormatter\ValueObjectFactory;

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
     * @var string
     */
    private $indentStyle = 'space';
    /**
     * @var int
     */
    private $indentSize = 2;
    /**
     * @var bool
     */
    private $insertFinalNewline = \true;
    public function __construct(string $indentStyle = 'space', int $indentSize = 2, bool $insertFinalNewline = \true)
    {
        $this->indentStyle = $indentStyle;
        $this->indentSize = $indentSize;
        $this->insertFinalNewline = $insertFinalNewline;
        $this->newLine = \Rector\FileFormatter\ValueObject\NewLine::fromEditorConfig('lf');
    }
    public static function create() : self
    {
        return new self();
    }
    public function withNewLine(\Rector\FileFormatter\ValueObject\NewLine $newLine) : self
    {
        $this->newLine = $newLine;
        return $this;
    }
    public function withIndent(\Rector\FileFormatter\ValueObject\Indent $indent) : self
    {
        $this->indentSize = $indent->getIndentSize();
        $this->indentStyle = $indent->getIndentStyle();
        return $this;
    }
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
        $this->newLine = \Rector\FileFormatter\ValueObject\NewLine::fromEditorConfig($endOfLine);
        return $this;
    }
    public function build() : \Rector\FileFormatter\ValueObject\EditorConfigConfiguration
    {
        $newLine = $this->newLine;
        return new \Rector\FileFormatter\ValueObject\EditorConfigConfiguration(\Rector\FileFormatter\ValueObject\Indent::fromSizeAndStyle($this->indentSize, $this->indentStyle), $newLine, $this->insertFinalNewline);
    }
}
