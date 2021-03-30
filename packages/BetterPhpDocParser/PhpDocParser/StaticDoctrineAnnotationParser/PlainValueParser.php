<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;

use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\Core\Exception\NotImplementedYetException;

final class PlainValueParser
{
    /**
     * @return bool|int|mixed|string
     */
    public function parseValue(BetterTokenIterator $tokenIterator)
    {
        $currentTokenValue = $tokenIterator->currentTokenValue();

        // consume the token
        $tokenIterator->next();

        // normalize value
        if ($currentTokenValue === 'false') {
            return false;
        }

        if ($currentTokenValue === 'true') {
            return true;
        }

        if (is_numeric($currentTokenValue) && (string) (int) $currentTokenValue === $currentTokenValue) {
            return (int) $currentTokenValue;
        }

        while ($tokenIterator->isCurrentTokenType(Lexer::TOKEN_DOUBLE_COLON) ||
            $tokenIterator->isCurrentTokenType(Lexer::TOKEN_IDENTIFIER)
        ) {
            $currentTokenValue .= $tokenIterator->currentTokenValue();
            $tokenIterator->next();
        }

        // nested entity!
        if ($tokenIterator->isCurrentTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
            // @todo
            throw new NotImplementedYetException('Add support for parsing of nested tag value nodes');
        }

        return $currentTokenValue;
    }
}
