<?php

declare (strict_types=1);
namespace RectorPrefix20211217\Helmich\TypoScriptParser\Tokenizer\Printer;

use RectorPrefix20211217\Helmich\TypoScriptParser\Tokenizer\TokenInterface;
class CodeTokenPrinter implements \RectorPrefix20211217\Helmich\TypoScriptParser\Tokenizer\Printer\TokenPrinterInterface
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
