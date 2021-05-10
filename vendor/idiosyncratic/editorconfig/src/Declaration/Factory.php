<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Idiosyncratic\EditorConfig\Declaration;

use function method_exists;
use function sprintf;
use function str_replace;
use function ucwords;
final class Factory
{
    /**
     * @param mixed $value
     */
    public function getDeclaration(string $name, $value) : Declaration
    {
        if ($value === 'unset') {
            return new UnsetDeclaration($name);
        }
        $method = sprintf('get%s', ucwords(str_replace(['-', '_'], '', $name)));
        if (method_exists($this, $method) === \true) {
            return $this->{$method}($value);
        }
        return new GenericDeclaration($name, $value);
    }
    /**
     * @param mixed $value
     */
    public function getIndentStyle($value) : IndentStyle
    {
        return new IndentStyle($value);
    }
    /**
     * @param mixed $value
     */
    public function getCharset($value) : Charset
    {
        return new Charset($value);
    }
    /**
     * @param mixed $value
     */
    public function getEndOfLine($value) : EndOfLine
    {
        return new EndOfLine($value);
    }
    /**
     * @param mixed $value
     */
    public function getInsertFinalNewline($value) : InsertFinalNewline
    {
        return new InsertFinalNewline($value);
    }
    /**
     * @param mixed $value
     */
    public function getTrimTrailingWhitespace($value) : TrimTrailingWhitespace
    {
        return new TrimTrailingWhitespace($value);
    }
    /**
     * @param mixed $value
     */
    public function getIndentSize($value) : IndentSize
    {
        return new IndentSize($value);
    }
    /**
     * @param mixed $value
     */
    public function getTabWidth($value) : TabWidth
    {
        return new TabWidth($value);
    }
    /**
     * @param mixed $value
     */
    public function getMaxLineLength($value) : MaxLineLength
    {
        return new MaxLineLength($value);
    }
}
