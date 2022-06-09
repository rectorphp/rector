<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Parser;

use PHPStan\PhpDocParser\Lexer\Lexer;
use function array_pop;
use function assert;
use function count;
use function in_array;
use function strlen;
class TokenIterator
{
    /** @var mixed[][] */
    private $tokens;
    /** @var int */
    private $index;
    /** @var int[] */
    private $savePoints = [];
    public function __construct(array $tokens, int $index = 0)
    {
        $this->tokens = $tokens;
        $this->index = $index;
        if ($this->tokens[$this->index][Lexer::TYPE_OFFSET] !== Lexer::TOKEN_HORIZONTAL_WS) {
            return;
        }
        $this->index++;
    }
    public function currentTokenValue() : string
    {
        return $this->tokens[$this->index][Lexer::VALUE_OFFSET];
    }
    public function currentTokenType() : int
    {
        return $this->tokens[$this->index][Lexer::TYPE_OFFSET];
    }
    public function currentTokenOffset() : int
    {
        $offset = 0;
        for ($i = 0; $i < $this->index; $i++) {
            $offset += strlen($this->tokens[$i][Lexer::VALUE_OFFSET]);
        }
        return $offset;
    }
    public function isCurrentTokenValue(string $tokenValue) : bool
    {
        return $this->tokens[$this->index][Lexer::VALUE_OFFSET] === $tokenValue;
    }
    public function isCurrentTokenType(int ...$tokenType) : bool
    {
        return in_array($this->tokens[$this->index][Lexer::TYPE_OFFSET], $tokenType, \true);
    }
    public function isPrecededByHorizontalWhitespace() : bool
    {
        return ($this->tokens[$this->index - 1][Lexer::TYPE_OFFSET] ?? -1) === Lexer::TOKEN_HORIZONTAL_WS;
    }
    /**
     * @throws ParserException
     */
    public function consumeTokenType(int $tokenType) : void
    {
        if ($this->tokens[$this->index][Lexer::TYPE_OFFSET] !== $tokenType) {
            $this->throwError($tokenType);
        }
        $this->index++;
        if (($this->tokens[$this->index][Lexer::TYPE_OFFSET] ?? -1) !== Lexer::TOKEN_HORIZONTAL_WS) {
            return;
        }
        $this->index++;
    }
    /**
     * @throws ParserException
     */
    public function consumeTokenValue(int $tokenType, string $tokenValue) : void
    {
        if ($this->tokens[$this->index][Lexer::TYPE_OFFSET] !== $tokenType || $this->tokens[$this->index][Lexer::VALUE_OFFSET] !== $tokenValue) {
            $this->throwError($tokenType, $tokenValue);
        }
        $this->index++;
        if (($this->tokens[$this->index][Lexer::TYPE_OFFSET] ?? -1) !== Lexer::TOKEN_HORIZONTAL_WS) {
            return;
        }
        $this->index++;
    }
    /** @phpstan-impure */
    public function tryConsumeTokenValue(string $tokenValue) : bool
    {
        if ($this->tokens[$this->index][Lexer::VALUE_OFFSET] !== $tokenValue) {
            return \false;
        }
        $this->index++;
        if ($this->tokens[$this->index][Lexer::TYPE_OFFSET] === Lexer::TOKEN_HORIZONTAL_WS) {
            $this->index++;
        }
        return \true;
    }
    /** @phpstan-impure */
    public function tryConsumeTokenType(int $tokenType) : bool
    {
        if ($this->tokens[$this->index][Lexer::TYPE_OFFSET] !== $tokenType) {
            return \false;
        }
        $this->index++;
        if ($this->tokens[$this->index][Lexer::TYPE_OFFSET] === Lexer::TOKEN_HORIZONTAL_WS) {
            $this->index++;
        }
        return \true;
    }
    public function getSkippedHorizontalWhiteSpaceIfAny() : string
    {
        if ($this->index > 0 && $this->tokens[$this->index - 1][Lexer::TYPE_OFFSET] === Lexer::TOKEN_HORIZONTAL_WS) {
            return $this->tokens[$this->index - 1][Lexer::VALUE_OFFSET];
        }
        return '';
    }
    /** @phpstan-impure */
    public function joinUntil(int ...$tokenType) : string
    {
        $s = '';
        while (!in_array($this->tokens[$this->index][Lexer::TYPE_OFFSET], $tokenType, \true)) {
            $s .= $this->tokens[$this->index++][Lexer::VALUE_OFFSET];
        }
        return $s;
    }
    public function next() : void
    {
        $this->index++;
        if ($this->tokens[$this->index][Lexer::TYPE_OFFSET] !== Lexer::TOKEN_HORIZONTAL_WS) {
            return;
        }
        $this->index++;
    }
    /** @phpstan-impure */
    public function forwardToTheEnd() : void
    {
        $lastToken = count($this->tokens) - 1;
        $this->index = $lastToken;
    }
    public function pushSavePoint() : void
    {
        $this->savePoints[] = $this->index;
    }
    public function dropSavePoint() : void
    {
        array_pop($this->savePoints);
    }
    public function rollback() : void
    {
        $index = array_pop($this->savePoints);
        assert($index !== null);
        $this->index = $index;
    }
    /**
     * @throws ParserException
     */
    private function throwError(int $expectedTokenType, ?string $expectedTokenValue = null) : void
    {
        throw new \PHPStan\PhpDocParser\Parser\ParserException($this->currentTokenValue(), $this->currentTokenType(), $this->currentTokenOffset(), $expectedTokenType, $expectedTokenValue);
    }
}
