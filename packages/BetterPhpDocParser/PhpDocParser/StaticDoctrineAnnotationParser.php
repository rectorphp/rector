<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TokenIterator;

/**
 * Better version of doctrine/annotation - with phpdoc-parser and  static reflection
 */
final class StaticDoctrineAnnotationParser
{
    /**
     * @var string[]
     */
    private const TAGS_NAMES = ['inject'];

    /**
     * mimics: https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/lib/Doctrine/Common/Annotations/DocParser.php#L1024-L1041
     * @return array<mixed, mixed>
     */
    public function resolveAnnotationMethodCall(TokenIterator $tokenIterator): array
    {
        if (! $tokenIterator->isCurrentTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
            return [];
        }

        $tokenIterator->consumeTokenType(Lexer::TOKEN_OPEN_PARENTHESES);

        // empty ()
        if ($tokenIterator->isCurrentTokenType(Lexer::TOKEN_CLOSE_PARENTHESES)) {
            return [];
        }

        return $this->resolveAnnotationValues($tokenIterator);
    }

    /**
     * @see https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/lib/Doctrine/Common/Annotations/DocParser.php#L1051-L1079
     * @return array<mixed, mixed>
     */
    private function resolveAnnotationValues(TokenIterator $tokenIterator): array
    {
        $values = $this->resolveAnnotationValue($tokenIterator);

        while ($tokenIterator->isCurrentTokenType(Lexer::TOKEN_COMMA)) {
            $tokenIterator->next();

            $nestedValues = $this->resolveAnnotationValue($tokenIterator);
            $values = array_merge($values, $nestedValues);
        }

        return $values;
    }

    /**
     * @see https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/lib/Doctrine/Common/Annotations/DocParser.php#L1215-L1224
     * @return array<int|string, mixed>
     */
    private function resolveAnnotationValue(TokenIterator $tokenIterator): array
    {
        // skips dummy tokens like newlines
        $tokenIterator->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);

        // is equal value or plain value?
        $keyValue = $tokenIterator->currentTokenValue();

        $tokenIterator->next();

        if (! $tokenIterator->isCurrentTokenType(Lexer::TOKEN_EQUAL)) {
            // 1. plain value - mimics https://github.com/doctrine/annotations/blob/0cb0cd2950a5c6cdbf22adbe2bfd5fd1ea68588f/lib/Doctrine/Common/Annotations/DocParser.php#L1234-L1282
            return [$keyValue];
        }

        // 2. assign key = value - mimics FieldAssignment() https://github.com/doctrine/annotations/blob/0cb0cd2950a5c6cdbf22adbe2bfd5fd1ea68588f/lib/Doctrine/Common/Annotations/DocParser.php#L1291-L1303
        $tokenIterator->consumeTokenType(Lexer::TOKEN_EQUAL);

        $currentTokenValue = $tokenIterator->currentTokenValue();

        // normalize value
        if ($currentTokenValue === 'false') {
            $currentTokenValue = false;
        }

        if ($currentTokenValue === 'true') {
            $currentTokenValue = true;
        }

        if (is_numeric($currentTokenValue)) {
            if ((string) (int) $currentTokenValue === $currentTokenValue) {
                $currentTokenValue = (int) $currentTokenValue;
            }
        }

        $tokenIterator->next();

        return [
            // plain token value
            $keyValue => $currentTokenValue,
        ];
    }
}
