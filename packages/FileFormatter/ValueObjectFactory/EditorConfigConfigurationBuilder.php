<?php

declare (strict_types=1);
namespace Rector\FileFormatter\ValueObjectFactory;

use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObject\NewLine;
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
     * @var bool
     */
    private $insertFinalNewline = \false;
    /**
     * @var NewLine
     */
    private $newLine;
    private function __construct()
    {
        $this->indentStyle = 'space';
        $this->indentSize = 2;
        $this->newLine = \Rector\FileFormatter\ValueObject\NewLine::fromEditorConfig('lf');
        $this->insertFinalNewline = \true;
    }
    /**
     * @return $this
     */
    public static function anEditorConfigConfiguration()
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
