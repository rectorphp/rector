<?php

declare (strict_types=1);
namespace RectorPrefix20211123\Helmich\TypoScriptParser\Tokenizer\Printer;

use RectorPrefix20211123\Helmich\TypoScriptParser\Tokenizer\TokenInterface;
class CodeTokenPrinter implements \RectorPrefix20211123\Helmich\TypoScriptParser\Tokenizer\Printer\TokenPrinterInterface
{
    /**
     * @param TokenInterface[] $tokens
     * @return string
     */
    public function printTokenStream($tokens) : string
    {
        $content = '';
        foreach ($tokens as $token) {
            $content .= $token->getValue();
        }
        return $content;
    }
}
