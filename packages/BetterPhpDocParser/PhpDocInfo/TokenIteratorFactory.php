<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo;

use RectorPrefix20220606\PHPStan\PhpDocParser\Lexer\Lexer;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\TokenIterator;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use RectorPrefix20220606\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
final class TokenIteratorFactory
{
    /**
     * @var string
     */
    private const INDEX = 'index';
    /**
     * @readonly
     * @var \PHPStan\PhpDocParser\Lexer\Lexer
     */
    private $lexer;
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    public function __construct(Lexer $lexer, PrivatesAccessor $privatesAccessor)
    {
        $this->lexer = $lexer;
        $this->privatesAccessor = $privatesAccessor;
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
        $tokens = $this->privatesAccessor->getPrivateProperty($tokenIterator, 'tokens');
        $betterTokenIterator = new BetterTokenIterator($tokens);
        // keep original position
        $currentIndex = $this->privatesAccessor->getPrivateProperty($tokenIterator, self::INDEX);
        $this->privatesAccessor->setPrivateProperty($betterTokenIterator, self::INDEX, $currentIndex);
        return $betterTokenIterator;
    }
}
