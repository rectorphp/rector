<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

use Nette\Utils\Strings;

class SkipNested
{
    /**
     * Skip all tokens for this annotation, so next annotation can work with tokens after this one
     * Inspired at @see \PHPStan\PhpDocParser\Parser\PhpDocParser::parseText()
     */
    public function resolveFromTokenIterator(TokenIterator $tokenIterator): string
    {
        $annotationContent = '';
        $unclosedOpenedBracketCount = 0;

        while (true) {
            if ($tokenIterator->currentTokenType() === Lexer::TOKEN_OPEN_PARENTHESES) {
                ++$unclosedOpenedBracketCount;
            }

            if ($tokenIterator->currentTokenType() === Lexer::TOKEN_CLOSE_PARENTHESES) {
                --$unclosedOpenedBracketCount;
            }

            if ($unclosedOpenedBracketCount === 0 && $tokenIterator->currentTokenType() === Lexer::TOKEN_PHPDOC_EOL) {
                break;
            }

            // remove new line "*"
            if (Strings::contains($tokenIterator->currentTokenValue(), '*')) {
                $tokenValueWithoutAsterisk = Strings::replace($tokenIterator->currentTokenValue(), '#\*#', '');
                $annotationContent .= $tokenValueWithoutAsterisk;
            } else {
                $annotationContent .= $tokenIterator->currentTokenValue();
            }

            // this is the end of single-line comment
            if ($tokenIterator->currentTokenType() === Lexer::TOKEN_END) {
                break;
            }

            $tokenIterator->next();
        }

        return $this->cleanMultilineAnnotationContent($annotationContent);
    }
}
