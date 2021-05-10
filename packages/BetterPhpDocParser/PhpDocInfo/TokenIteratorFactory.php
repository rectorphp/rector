<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class TokenIteratorFactory
{
    /**
     * @var string
     */
    private const INDEX = 'index';

    public function __construct(
        private Lexer $lexer,
        private PrivatesAccessor $privatesAccessor
    ) {
    }

    public function create(string $content): BetterTokenIterator
    {
        $tokens = $this->lexer->tokenize($content);
        return new BetterTokenIterator($tokens);
    }

    public function createFromTokenIterator(TokenIterator $tokenIterator): BetterTokenIterator
    {
        if ($tokenIterator instanceof BetterTokenIterator) {
            return $tokenIterator;
        }

        $tokens = $this->privatesAccessor->getPrivateProperty($tokenIterator, 'tokens');
        $betterTokenIterator = new BetterTokenIterator($tokens);

        // keep original position
        $currentIndex = $this->privatesAccessor->getPrivateProperty($tokenIterator, 'index');
        $this->privatesAccessor->setPrivateProperty($betterTokenIterator, self::INDEX, $currentIndex);

        return $betterTokenIterator;
    }
}
