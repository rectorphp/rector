<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;

use PHPStan\PhpDocParser\Parser\TokenIterator;
final class NextTokenType
{
    /**
     * Is the token after the current one of one of these types? The iterator does not move.
     */
    public static function is(TokenIterator $tokenIterator, int ...$tokenTypes): bool
    {
        if (!isset($tokenIterator->getTokens()[$tokenIterator->currentTokenIndex() + 1])) {
            return \false;
        }
        $tokenIterator->pushSavePoint();
        $tokenIterator->next();
        $isNextTokenType = $tokenIterator->isCurrentTokenType(...$tokenTypes);
        $tokenIterator->rollback();
        return $isNextTokenType;
    }
}
