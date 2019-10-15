<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TokenIterator;

final class TokenIteratorFactory
{
    /**
     * @var Lexer
     */
    private $lexer;

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
    }

    public function create(string $content): TokenIterator
    {
        $tokens = $this->lexer->tokenize($content);

        return new TokenIterator($tokens);
    }
}
