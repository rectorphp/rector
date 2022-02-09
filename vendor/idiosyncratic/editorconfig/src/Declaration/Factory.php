<?php

declare (strict_types=1);
namespace RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration;

use function method_exists;
use function sprintf;
use function str_replace;
use function ucwords;
final class Factory
{
    /**
     * @param mixed $value
     */
    public function getDeclaration(string $name, $value) : \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\Declaration
    {
        if ($value === 'unset') {
            return new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\UnsetDeclaration($name);
        }
        $method = \sprintf('get%s', \ucwords(\str_replace(['-', '_'], '', $name)));
        if (\method_exists($this, $method) === \true) {
            return $this->{$method}($value);
        }
        return new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\GenericDeclaration($name, $value);
    }
    /**
     * @param mixed $value
     */
    public function getIndentStyle($value) : \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\IndentStyle
    {
        return new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\IndentStyle($value);
    }
    /**
     * @param mixed $value
     */
    public function getCharset($value) : \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\Charset
    {
        return new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\Charset($value);
    }
    /**
     * @param mixed $value
     */
    public function getEndOfLine($value) : \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\EndOfLine
    {
        return new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\EndOfLine($value);
    }
    /**
     * @param mixed $value
     */
    public function getInsertFinalNewline($value) : \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\InsertFinalNewline
    {
        return new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\InsertFinalNewline($value);
    }
    /**
     * @param mixed $value
     */
    public function getTrimTrailingWhitespace($value) : \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\TrimTrailingWhitespace
    {
        return new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\TrimTrailingWhitespace($value);
    }
    /**
     * @param mixed $value
     */
    public function getIndentSize($value) : \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\IndentSize
    {
        return new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\IndentSize($value);
    }
    /**
     * @param mixed $value
     */
    public function getTabWidth($value) : \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\TabWidth
    {
        return new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\TabWidth($value);
    }
    /**
     * @param mixed $value
     */
    public function getMaxLineLength($value) : \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\MaxLineLength
    {
        return new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\MaxLineLength($value);
    }
}
