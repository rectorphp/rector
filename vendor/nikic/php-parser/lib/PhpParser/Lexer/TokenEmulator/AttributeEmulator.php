<?php

declare (strict_types=1);
namespace PhpParser\Lexer\TokenEmulator;

use PhpParser\Lexer\Emulative;
final class AttributeEmulator extends \PhpParser\Lexer\TokenEmulator\TokenEmulator
{
    public function getPhpVersion() : string
    {
        return \PhpParser\Lexer\Emulative::PHP_8_0;
    }
    /**
     * @param string $code
     */
    public function isEmulationNeeded($code) : bool
    {
        return \strpos($code, '#[') !== \false;
    }
    /**
     * @param string $code
     * @param mixed[] $tokens
     */
    public function emulate($code, $tokens) : array
    {
        // We need to manually iterate and manage a count because we'll change
        // the tokens array on the way.
        $line = 1;
        for ($i = 0, $c = \count($tokens); $i < $c; ++$i) {
            if ($tokens[$i] === '#' && isset($tokens[$i + 1]) && $tokens[$i + 1] === '[') {
                \array_splice($tokens, $i, 2, [[\T_ATTRIBUTE, '#[', $line]]);
                $c--;
                continue;
            }
            if (\is_array($tokens[$i])) {
                $line += \substr_count($tokens[$i][1], "\n");
            }
        }
        return $tokens;
    }
    /**
     * @param string $code
     * @param mixed[] $tokens
     */
    public function reverseEmulate($code, $tokens) : array
    {
        // TODO
        return $tokens;
    }
    /**
     * @param string $code
     * @param mixed[] $patches
     */
    public function preprocessCode($code, &$patches) : string
    {
        $pos = 0;
        while (\false !== ($pos = \strpos($code, '#[', $pos))) {
            // Replace #[ with %[
            $code[$pos] = '%';
            $patches[] = [$pos, 'replace', '#'];
            $pos += 2;
        }
        return $code;
    }
}
