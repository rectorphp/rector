<?php

declare (strict_types=1);
namespace PhpParser\Lexer\TokenEmulator;

use PhpParser\PhpVersion;
use PhpParser\Token;
class ExplicitOctalEmulator extends \PhpParser\Lexer\TokenEmulator\TokenEmulator
{
    public function getPhpVersion() : PhpVersion
    {
        return PhpVersion::fromComponents(8, 1);
    }
    public function isEmulationNeeded(string $code) : bool
    {
        return \strpos($code, '0o') !== \false || \strpos($code, '0O') !== \false;
    }
    public function emulate(string $code, array $tokens) : array
    {
        for ($i = 0, $c = \count($tokens); $i < $c; ++$i) {
            $token = $tokens[$i];
            if ((\is_array($token) ? $token[0] : $token) == \T_LNUMBER && (\is_array($token) ? $token[1] : $token) === '0' && isset($tokens[$i + 1]) && (\is_array($tokens[$i + 1]) ? $tokens[$i + 1][0] : $tokens[$i + 1]) == \T_STRING && \preg_match('/[oO][0-7]+(?:_[0-7]+)*/', \is_array($tokens[$i + 1]) ? $tokens[$i + 1][1] : $tokens[$i + 1])) {
                $tokenKind = $this->resolveIntegerOrFloatToken(\is_array($tokens[$i + 1]) ? $tokens[$i + 1][1] : $tokens[$i + 1]);
                \array_splice($tokens, $i, 2, [new Token($tokenKind, '0' . (\is_array($tokens[$i + 1]) ? $tokens[$i + 1][1] : $tokens[$i + 1]), $token->line, $token->pos)]);
                $c--;
            }
        }
        return $tokens;
    }
    private function resolveIntegerOrFloatToken(string $str) : int
    {
        $str = \substr($str, 1);
        $str = \str_replace('_', '', $str);
        $num = \octdec($str);
        return \is_float($num) ? \T_DNUMBER : \T_LNUMBER;
    }
    public function reverseEmulate(string $code, array $tokens) : array
    {
        // Explicit octals were not legal code previously, don't bother.
        return $tokens;
    }
}
