<?php

declare (strict_types=1);
namespace RectorPrefix20220531\Helmich\TypoScriptParser\Parser;

use BadMethodCallException;
use RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\Token;
use RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\TokenInterface;
use Iterator;
/**
 * Helper class that represents a token stream
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser
 */
class TokenStream implements \Iterator, \ArrayAccess
{
    /** @var array */
    private $tokens;
    /** @var int */
    private $index = 0;
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }
    /**
     * @param int $lookAhead
     * @return TokenInterface
     */
    public function current(int $lookAhead = 0) : \RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\TokenInterface
    {
        return $this[$this->index + $lookAhead];
    }
    /**
     * @param int $increment
     * @return void
     */
    public function next(int $increment = 1) : void
    {
        if ($this->index < \count($this->tokens)) {
            $this->index += $increment;
        }
    }
    /**
     * @return bool
     */
    public function valid() : bool
    {
        return $this->index < \count($this->tokens);
    }
    /**
     * @return void
     */
    public function rewind() : void
    {
        $this->index = 0;
    }
    /**
     * @return int
     */
    public function key() : int
    {
        return $this->index;
    }
    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return $offset >= 0 && $offset < \count($this->tokens);
    }
    /**
     * @param int $offset
     * @return TokenInterface
     */
    public function offsetGet($offset) : \RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\TokenInterface
    {
        return $this->tokens[$offset];
    }
    /**
     * @param int            $offset
     * @param TokenInterface $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('changing a token stream is not permitted');
    }
    /**
     * @param int $offset
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('changing a token stream is not permitted');
    }
    /**
     * Normalizes the token stream.
     *
     * This method transforms the token stream in a normalized form. This
     * includes:
     *
     *   - trimming whitespaces (remove leading and trailing whitespaces, as
     *     those are irrelevant for the parser)
     *   - remove both one-line and multi-line comments (also irrelevant for the
     *     parser)
     *
     * @return TokenStream
     */
    public function normalized() : \RectorPrefix20220531\Helmich\TypoScriptParser\Parser\TokenStream
    {
        $filteredTokens = [];
        $maxLine = 0;
        foreach ($this->tokens as $token) {
            $maxLine = (int) \max($token->getLine(), $maxLine);
            // Trim unnecessary whitespace, but leave line breaks! These are important!
            if ($token->getType() === \RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\TokenInterface::TYPE_WHITESPACE) {
                $value = \trim($token->getValue(), "\t ");
                if (\strlen($value) > 0) {
                    $filteredTokens[] = new \RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\TokenInterface::TYPE_WHITESPACE, $value, $token->getLine(), $token->getColumn());
                }
            } else {
                $filteredTokens[] = $token;
            }
        }
        // Add two linebreak tokens; during parsing, we usually do not look more than two
        // tokens ahead; this hack ensures that there will always be at least two more tokens
        // present and we do not have to check whether these tokens exists.
        $filteredTokens[] = new \RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\TokenInterface::TYPE_WHITESPACE, "\n", $maxLine + 1, 1);
        $filteredTokens[] = new \RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\TokenInterface::TYPE_WHITESPACE, "\n", $maxLine + 2, 1);
        return new \RectorPrefix20220531\Helmich\TypoScriptParser\Parser\TokenStream($filteredTokens);
    }
}
