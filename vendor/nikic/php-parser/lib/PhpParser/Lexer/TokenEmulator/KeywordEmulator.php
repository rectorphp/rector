<?php

declare (strict_types=1);
namespace PhpParser\Lexer\TokenEmulator;

use PhpParser\Token;
abstract class KeywordEmulator extends \PhpParser\Lexer\TokenEmulator\TokenEmulator
{
    public abstract function getKeywordString() : string;
    public abstract function getKeywordToken() : int;
    public function isEmulationNeeded(string $code) : bool
    {
        return \strpos(\strtolower($code), $this->getKeywordString()) !== \false;
    }
    /** @param Token[] $tokens */
    protected function isKeywordContext(array $tokens, int $pos) : bool
    {
        $prevToken = $this->getPreviousNonSpaceToken($tokens, $pos);
        if ($prevToken === null) {
            return \false;
        }
        return (\is_array($prevToken) ? $prevToken[0] : $prevToken) !== \T_OBJECT_OPERATOR && (\is_array($prevToken) ? $prevToken[0] : $prevToken) !== \T_NULLSAFE_OBJECT_OPERATOR;
    }
    public function emulate(string $code, array $tokens) : array
    {
        $keywordString = $this->getKeywordString();
        foreach ($tokens as $i => $token) {
            if ((\is_array($token) ? $token[0] : $token) === \T_STRING && \strtolower(\is_array($token) ? $token[1] : $token) === $keywordString && $this->isKeywordContext($tokens, $i)) {
                \is_array($token) ? $token[0] : ($token = $this->getKeywordToken());
            }
        }
        return $tokens;
    }
    /** @param Token[] $tokens */
    private function getPreviousNonSpaceToken(array $tokens, int $start) : ?Token
    {
        for ($i = $start - 1; $i >= 0; --$i) {
            if ((\is_array($tokens[$i]) ? $tokens[$i][0] : $tokens[$i]) === \T_WHITESPACE) {
                continue;
            }
            return $tokens[$i];
        }
        return null;
    }
    public function reverseEmulate(string $code, array $tokens) : array
    {
        $keywordToken = $this->getKeywordToken();
        foreach ($tokens as $token) {
            if ((\is_array($token) ? $token[0] : $token) === $keywordToken) {
                \is_array($token) ? $token[0] : ($token = \T_STRING);
            }
        }
        return $tokens;
    }
}
