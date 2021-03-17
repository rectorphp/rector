<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class SkipCorrectIterable
{
    /**
     * @param Tokens<Token> $tokens
     */
    public function getNextMeaningfulToken(Tokens $tokens, int $index): ?Token
    {
        $nextMeaninfulTokenPosition = $tokens->getNextMeaningfulToken($index);
        if ($nextMeaninfulTokenPosition === null) {
            return null;
        }

        return $tokens[$index];
    }
}
