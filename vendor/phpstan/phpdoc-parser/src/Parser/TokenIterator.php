<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Parser;

use LogicException;
use PHPStan\PhpDocParser\Lexer\Lexer;
use function array_pop;
use function assert;
use function count;
use function in_array;
use function strlen;
use function substr;
class TokenIterator
{
    /** @var list<array{string, int, int}> */
    private $tokens;
    /** @var int */
    private $index;
    /** @var int[] */
    private $savePoints = [];
    /** @var list<int> */
    private $skippedTokenTypes = [Lexer::TOKEN_HORIZONTAL_WS];
    /** @var string|null */
    private $newline = null;
    /**
     * @param list<array{string, int, int}> $tokens
     */
    public function __construct(array $tokens, int $index = 0)
    {
        $this->tokens = $tokens;
        $this->index = $index;
        $this->skipIrrelevantTokens();
    }
    /**
     * @return list<array{string, int, int}>
     */
    public function getTokens() : array
    {
        return $this->tokens;
    }
    public function getContentBetween(int $startPos, int $endPos) : string
    {
        if ($startPos < 0 || $endPos > count($this->tokens)) {
            throw new LogicException();
        }
        $content = '';
        for ($i = $startPos; $i < $endPos; $i++) {
            $content .= $this->tokens[$i][Lexer::VALUE_OFFSET];
        }
        return $content;
    }
    public function getTokenCount() : int
    {
        return count($this->tokens);
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
    public function currentTokenLine() : int
    {
        return $this->tokens[$this->index][Lexer::LINE_OFFSET];
    }
    public function currentTokenIndex() : int
    {
        return $this->index;
    }
    public function endIndexOfLastRelevantToken() : int
    {
        $endIndex = $this->currentTokenIndex();
        $endIndex--;
        while (in_array($this->tokens[$endIndex][Lexer::TYPE_OFFSET], $this->skippedTokenTypes, \true)) {
            if (!isset($this->tokens[$endIndex - 1])) {
                break;
            }
            $endIndex--;
        }
        return $endIndex;
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
        if ($tokenType === Lexer::TOKEN_PHPDOC_EOL) {
            if ($this->newline === null) {
                $this->detectNewline();
            }
        }
        $this->index++;
        $this->skipIrrelevantTokens();
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
        $this->skipIrrelevantTokens();
    }
    /** @phpstan-impure */
    public function tryConsumeTokenValue(string $tokenValue) : bool
    {
        if ($this->tokens[$this->index][Lexer::VALUE_OFFSET] !== $tokenValue) {
            return \false;
        }
        $this->index++;
        $this->skipIrrelevantTokens();
        return \true;
    }
    /** @phpstan-impure */
    public function tryConsumeTokenType(int $tokenType) : bool
    {
        if ($this->tokens[$this->index][Lexer::TYPE_OFFSET] !== $tokenType) {
            return \false;
        }
        if ($tokenType === Lexer::TOKEN_PHPDOC_EOL) {
            if ($this->newline === null) {
                $this->detectNewline();
            }
        }
        $this->index++;
        $this->skipIrrelevantTokens();
        return \true;
    }
    private function detectNewline() : void
    {
        $value = $this->currentTokenValue();
        if (substr($value, 0, 2) === "\r\n") {
            $this->newline = "\r\n";
        } elseif (substr($value, 0, 1) === "\n") {
            $this->newline = "\n";
        }
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
        $this->skipIrrelevantTokens();
    }
    private function skipIrrelevantTokens() : void
    {
        if (!isset($this->tokens[$this->index])) {
            return;
        }
        while (in_array($this->tokens[$this->index][Lexer::TYPE_OFFSET], $this->skippedTokenTypes, \true)) {
            if (!isset($this->tokens[$this->index + 1])) {
                break;
            }
            $this->index++;
        }
    }
    public function addEndOfLineToSkippedTokens() : void
    {
        $this->skippedTokenTypes = [Lexer::TOKEN_HORIZONTAL_WS, Lexer::TOKEN_PHPDOC_EOL];
    }
    public function removeEndOfLineFromSkippedTokens() : void
    {
        $this->skippedTokenTypes = [Lexer::TOKEN_HORIZONTAL_WS];
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
        throw new \PHPStan\PhpDocParser\Parser\ParserException($this->currentTokenValue(), $this->currentTokenType(), $this->currentTokenOffset(), $expectedTokenType, $expectedTokenValue, $this->currentTokenLine());
    }
    /**
     * Check whether the position is directly preceded by a certain token type.
     *
     * During this check TOKEN_HORIZONTAL_WS and TOKEN_PHPDOC_EOL are skipped
     */
    public function hasTokenImmediatelyBefore(int $pos, int $expectedTokenType) : bool
    {
        $tokens = $this->tokens;
        $pos--;
        for (; $pos >= 0; $pos--) {
            $token = $tokens[$pos];
            $type = $token[Lexer::TYPE_OFFSET];
            if ($type === $expectedTokenType) {
                return \true;
            }
            if (!in_array($type, [Lexer::TOKEN_HORIZONTAL_WS, Lexer::TOKEN_PHPDOC_EOL], \true)) {
                break;
            }
        }
        return \false;
    }
    /**
     * Check whether the position is directly followed by a certain token type.
     *
     * During this check TOKEN_HORIZONTAL_WS and TOKEN_PHPDOC_EOL are skipped
     */
    public function hasTokenImmediatelyAfter(int $pos, int $expectedTokenType) : bool
    {
        $tokens = $this->tokens;
        $pos++;
        for ($c = count($tokens); $pos < $c; $pos++) {
            $token = $tokens[$pos];
            $type = $token[Lexer::TYPE_OFFSET];
            if ($type === $expectedTokenType) {
                return \true;
            }
            if (!in_array($type, [Lexer::TOKEN_HORIZONTAL_WS, Lexer::TOKEN_PHPDOC_EOL], \true)) {
                break;
            }
        }
        return \false;
    }
    public function getDetectedNewline() : ?string
    {
        return $this->newline;
    }
    /**
     * Whether the given position is immediately surrounded by parenthesis.
     */
    public function hasParentheses(int $startPos, int $endPos) : bool
    {
        return $this->hasTokenImmediatelyBefore($startPos, Lexer::TOKEN_OPEN_PARENTHESES) && $this->hasTokenImmediatelyAfter($endPos, Lexer::TOKEN_CLOSE_PARENTHESES);
    }
}
