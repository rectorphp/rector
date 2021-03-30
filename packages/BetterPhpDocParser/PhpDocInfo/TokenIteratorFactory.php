<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;

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

    public function create(string $content): BetterTokenIterator
    {
        $tokens = $this->lexer->tokenize($content);
        return new BetterTokenIterator($tokens);
    }
}
