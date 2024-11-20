<?php

declare (strict_types=1);
namespace PhpParser\Lexer\TokenEmulator;

use PhpParser\PhpVersion;
use PhpParser\Token;
final class AsymmetricVisibilityTokenEmulator extends \PhpParser\Lexer\TokenEmulator\TokenEmulator
{
    public function getPhpVersion() : PhpVersion
    {
        return PhpVersion::fromComponents(8, 4);
    }
    public function isEmulationNeeded(string $code) : bool
    {
        $code = \strtolower($code);
        return \strpos($code, 'public(set)') !== \false || \strpos($code, 'protected(set)') !== \false || \strpos($code, 'private(set)') !== \false;
    }
    public function emulate(string $code, array $tokens) : array
    {
        $map = [\T_PUBLIC => \T_PUBLIC_SET, \T_PROTECTED => \T_PROTECTED_SET, \T_PRIVATE => \T_PRIVATE_SET];
        for ($i = 0, $c = \count($tokens); $i < $c; ++$i) {
            $token = $tokens[$i];
            if (isset($map[\is_array($token) ? $token[0] : $token]) && $i + 3 < $c && (\is_array($tokens[$i + 1]) ? $tokens[$i + 1][1] : $tokens[$i + 1]) === '(' && (\is_array($tokens[$i + 2]) ? $tokens[$i + 2][0] : $tokens[$i + 2]) === \T_STRING && \strtolower(\is_array($tokens[$i + 2]) ? $tokens[$i + 2][1] : $tokens[$i + 2]) === 'set' && (\is_array($tokens[$i + 3]) ? $tokens[$i + 3][1] : $tokens[$i + 3]) === ')' && $this->isKeywordContext($tokens, $i)) {
                \array_splice($tokens, $i, 4, [new Token($map[\is_array($token) ? $token[0] : $token], (\is_array($token) ? $token[1] : $token) . '(' . (\is_array($tokens[$i + 2]) ? $tokens[$i + 2][1] : $tokens[$i + 2]) . ')', $token->line, $token->pos)]);
                $c -= 3;
            }
        }
        return $tokens;
    }
    public function reverseEmulate(string $code, array $tokens) : array
    {
        $reverseMap = [\T_PUBLIC_SET => \T_PUBLIC, \T_PROTECTED_SET => \T_PROTECTED, \T_PRIVATE_SET => \T_PRIVATE];
        for ($i = 0, $c = \count($tokens); $i < $c; ++$i) {
            $token = $tokens[$i];
            if (isset($reverseMap[\is_array($token) ? $token[0] : $token]) && \preg_match('/(public|protected|private)\\((set)\\)/i', \is_array($token) ? $token[1] : $token, $matches)) {
                [, $modifier, $set] = $matches;
                $modifierLen = \strlen($modifier);
                \array_splice($tokens, $i, 1, [new Token($reverseMap[\is_array($token) ? $token[0] : $token], $modifier, $token->line, $token->pos), new Token(\ord('('), '(', $token->line, $token->pos + $modifierLen), new Token(\T_STRING, $set, $token->line, $token->pos + $modifierLen + 1), new Token(\ord(')'), ')', $token->line, $token->pos + $modifierLen + 4)]);
                $i += 3;
                $c += 3;
            }
        }
        return $tokens;
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
}
