<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Helmich\TypoScriptParser\Tokenizer;

use ArrayObject;
/**
 * Helper class for building a token stream
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Tokenizer
 */
class TokenStreamBuilder
{
    /** @var ArrayObject */
    private $tokens;
    /** @var int|null */
    private $currentLine = null;
    /** @var int */
    private $currentColumn = 1;
    /**
     * TokenStreamBuilder constructor.
     */
    public function __construct()
    {
        $this->tokens = new \ArrayObject();
    }
    /**
     * Appends a new token to the token stream
     *
     * @param string $type           Token type
     * @param string $value          Token value
     * @param int    $line           Line in source code
     * @param array  $patternMatches Subpattern matches
     * @return void
     */
    public function append($type, $value, $line, $patternMatches = []) : void
    {
        if ($this->currentLine !== $line) {
            $this->currentLine = $line;
            $this->currentColumn = 1;
        }
        $this->tokens->append(new \RectorPrefix20211020\Helmich\TypoScriptParser\Tokenizer\Token($type, $value, $line, $this->currentColumn, $patternMatches));
        $this->currentColumn += \strlen($value);
    }
    /**
     * Appends a new token to the token stream
     *
     * @param TokenInterface $token The token to append
     * @return void
     */
    public function appendToken($token) : void
    {
        $this->tokens->append($token);
    }
    /**
     * @return int The length of the token stream
     */
    public function count() : int
    {
        return $this->tokens->count();
    }
    /**
     * @return int
     */
    public function currentColumn() : int
    {
        return $this->currentColumn;
    }
    /**
     * @return ArrayObject The completed token stream
     */
    public function build() : \ArrayObject
    {
        return $this->tokens;
    }
}
