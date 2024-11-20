<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
final class TokenIteratorFactory
{
    /**
     * @readonly
     */
    private Lexer $lexer;
    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
    }
    public function create(string $content) : BetterTokenIterator
    {
        $tokens = $this->lexer->tokenize($content);
        return new BetterTokenIterator($tokens);
    }
    public function createFromTokenIterator(TokenIterator $tokenIterator) : BetterTokenIterator
    {
        if ($tokenIterator instanceof BetterTokenIterator) {
            return $tokenIterator;
        }
        // keep original tokens and index position
        $tokens = $tokenIterator->getTokens();
        $currentIndex = $tokenIterator->currentTokenIndex();
        return new BetterTokenIterator($tokens, $currentIndex);
    }
}
