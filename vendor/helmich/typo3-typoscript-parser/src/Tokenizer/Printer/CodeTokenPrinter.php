<?php

declare (strict_types=1);
namespace RectorPrefix20220527\Helmich\TypoScriptParser\Tokenizer\Printer;

use RectorPrefix20220527\Helmich\TypoScriptParser\Tokenizer\TokenInterface;
class CodeTokenPrinter implements TokenPrinterInterface
{
    /**
     * @param TokenInterface[] $tokens
     * @return string
     */
    public function printTokenStream(array $tokens) : string
    {
        $content = '';
        foreach ($tokens as $token) {
            $content .= $token->getValue();
        }
        return $content;
    }
}
