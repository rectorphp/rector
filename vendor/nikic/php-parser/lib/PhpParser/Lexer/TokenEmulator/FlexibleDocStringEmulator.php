<?php

declare (strict_types=1);
namespace PhpParser\Lexer\TokenEmulator;

use PhpParser\Lexer\Emulative;
final class FlexibleDocStringEmulator extends \PhpParser\Lexer\TokenEmulator\TokenEmulator
{
    const FLEXIBLE_DOC_STRING_REGEX = <<<'REGEX'
/<<<[ \t]*(['"]?)([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)\1\r?\n
(?:.*\r?\n)*?
(?<indentation>\h*)\2(?![a-zA-Z0-9_\x80-\xff])(?<separator>(?:;?[\r\n])?)/x
REGEX;
    public function getPhpVersion() : string
    {
        return \PhpParser\Lexer\Emulative::PHP_7_3;
    }
    /**
     * @param string $code
     */
    public function isEmulationNeeded($code) : bool
    {
        return \strpos($code, '<<<') !== \false;
    }
    /**
     * @param string $code
     * @param mixed[] $tokens
     */
    public function emulate($code, $tokens) : array
    {
        // Handled by preprocessing + fixup.
        return $tokens;
    }
    /**
     * @param string $code
     * @param mixed[] $tokens
     */
    public function reverseEmulate($code, $tokens) : array
    {
        // Not supported.
        return $tokens;
    }
    /**
     * @param string $code
     * @param mixed[] $patches
     */
    public function preprocessCode($code, &$patches) : string
    {
        if (!\preg_match_all(self::FLEXIBLE_DOC_STRING_REGEX, $code, $matches, \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE)) {
            // No heredoc/nowdoc found
            return $code;
        }
        // Keep track of how much we need to adjust string offsets due to the modifications we
        // already made
        $posDelta = 0;
        foreach ($matches as $match) {
            $indentation = $match['indentation'][0];
            $indentationStart = $match['indentation'][1];
            $separator = $match['separator'][0];
            $separatorStart = $match['separator'][1];
            if ($indentation === '' && $separator !== '') {
                // Ordinary heredoc/nowdoc
                continue;
            }
            if ($indentation !== '') {
                // Remove indentation
                $indentationLen = \strlen($indentation);
                $code = \substr_replace($code, '', $indentationStart + $posDelta, $indentationLen);
                $patches[] = [$indentationStart + $posDelta, 'add', $indentation];
                $posDelta -= $indentationLen;
            }
            if ($separator === '') {
                // Insert newline as separator
                $code = \substr_replace($code, "\n", $separatorStart + $posDelta, 0);
                $patches[] = [$separatorStart + $posDelta, 'remove', "\n"];
                $posDelta += 1;
            }
        }
        return $code;
    }
}
