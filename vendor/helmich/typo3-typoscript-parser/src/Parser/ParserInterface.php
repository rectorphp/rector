<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Helmich\TypoScriptParser\Parser;

use Helmich\TypoScriptParser\Parser\AST\Statement;
interface ParserInterface
{
    /**
     * Parses a stream resource.
     *
     * This can be any kind of stream supported by PHP (e.g. a filename or a URL).
     *
     * @param string $stream The stream resource.
     * @return Statement[] The syntax tree.
     */
    public function parseStream($stream) : array;
    /**
     * Parses a TypoScript string.
     *
     * @param string $string The string to parse.
     * @return Statement[] The syntax tree.
     */
    public function parseString($string) : array;
    /**
     * Parses a token stream.
     *
     * @param \Helmich\TypoScriptParser\Tokenizer\TokenInterface[] $tokens The token stream to parse.
     * @return Statement[] The syntax tree.
     */
    public function parseTokens($tokens) : array;
}
