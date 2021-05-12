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
    /**
     * @return $this
     */
    public static function create()
    {
        return new self();
    }
    /**
     * @return $this
     */
    public function withNewLine(\Rector\FileFormatter\ValueObject\NewLine $newLine)
    {
        $this->newLine = $newLine;
        return $this;
    }
    /**
     * @return $this
     */
    public function withIndent(\Rector\FileFormatter\ValueObject\Indent $indent)
    {
        $this->indentSize = $indent->getIndentSize();
        $this->indentStyle = $indent->getIndentStyle();
        return $this;
    }
    /**
     * @return $this
     */
    public function withIndentStyle(string $indentStyle)
    {
        $this->indentStyle = $indentStyle;
        return $this;
    }
    /**
     * @return $this
     */
    public function withIndentSize(int $indentSize)
    {
        $this->indentSize = $indentSize;
        return $this;
    }
    /**
     * @return $this
     */
    public function withInsertFinalNewline(bool $insertFinalNewline)
    {
        $this->insertFinalNewline = $insertFinalNewline;
        return $this;
    }
    /**
     * @return $this
     */
    public function withEndOfLineFromEditorConfig(string $endOfLine)
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
