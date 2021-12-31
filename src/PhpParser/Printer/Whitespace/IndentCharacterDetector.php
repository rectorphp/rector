<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Printer\Whitespace;

use RectorPrefix20211231\Nette\Utils\Strings;
final class IndentCharacterDetector
{
    /**
     * Solves https://github.com/rectorphp/rector/issues/1964
     *
     * Some files have spaces, some have tabs. Keep the original indent if possible.
     *
     * @param mixed[] $tokens
     */
    public function detect(array $tokens) : string
    {
        foreach ($tokens as $token) {
            if ($token[0] === \T_WHITESPACE) {
                $tokenContent = $token[1];
                $tabMatches = \RectorPrefix20211231\Nette\Utils\Strings::matchAll($tokenContent, '#^\\t#m');
                if ($tabMatches !== []) {
                    return "\t";
                }
            }
        }
        // use space by default
        return ' ';
    }
}
