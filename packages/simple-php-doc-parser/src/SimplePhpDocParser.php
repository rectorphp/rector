<?php

declare(strict_types=1);

namespace Rector\SimplePhpDocParser;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;

/**
 * @see \Rector\SimplePhpDocParser\Tests\SimplePhpDocParser\SimplePhpDocParserTest
 */
final class SimplePhpDocParser
{
    /**
     * @var PhpDocParser
     */
    private $phpDocParser;

    /**
     * @var Lexer
     */
    private $lexer;

    public function __construct(PhpDocParser $phpDocParser, Lexer $lexer)
    {
        $this->phpDocParser = $phpDocParser;
        $this->lexer = $lexer;
    }

    public function parseDocBlock(string $docBlock): PhpDocNode
    {
        $tokens = $this->lexer->tokenize($docBlock);
        $tokenIterator = new TokenIterator($tokens);

        return $this->phpDocParser->parse($tokenIterator);
    }
}
