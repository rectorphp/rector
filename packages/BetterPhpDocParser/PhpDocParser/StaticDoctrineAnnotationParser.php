<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser;

use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\PlainValueParser;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
/**
 * Better version of doctrine/annotation - with phpdoc-parser and  static reflection
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\StaticDoctrineAnnotationParserTest
 */
final class StaticDoctrineAnnotationParser
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\PlainValueParser
     */
    private $plainValueParser;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser
     */
    private $arrayParser;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\PlainValueParser $plainValueParser, \Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser $arrayParser)
    {
        $this->plainValueParser = $plainValueParser;
        $this->arrayParser = $arrayParser;
    }
    /**
     * mimics: https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/lib/Doctrine/Common/Annotations/DocParser.php#L1024-L1041
     * @return array<mixed, mixed>
     */
    public function resolveAnnotationMethodCall(\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator $tokenIterator) : array
    {
        if (!$tokenIterator->isCurrentTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_OPEN_PARENTHESES)) {
            return [];
        }
        $tokenIterator->consumeTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_OPEN_PARENTHESES);
        // empty ()
        if ($tokenIterator->isCurrentTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_CLOSE_PARENTHESES)) {
            return [];
        }
        return $this->resolveAnnotationValues($tokenIterator);
    }
    /**
     * @see https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/lib/Doctrine/Common/Annotations/DocParser.php#L1215-L1224
     * @return mixed[]|\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode|\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode|string
     */
    public function resolveAnnotationValue(\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator $tokenIterator)
    {
        // skips dummy tokens like newlines
        $tokenIterator->tryConsumeTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_PHPDOC_EOL);
        // no assign
        if (!$tokenIterator->isNextTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_EQUAL)) {
            // 1. plain value - mimics https://github.com/doctrine/annotations/blob/0cb0cd2950a5c6cdbf22adbe2bfd5fd1ea68588f/lib/Doctrine/Common/Annotations/DocParser.php#L1234-L1282
            return $this->parseValue($tokenIterator);
        }
        // 2. assign key = value - mimics FieldAssignment() https://github.com/doctrine/annotations/blob/0cb0cd2950a5c6cdbf22adbe2bfd5fd1ea68588f/lib/Doctrine/Common/Annotations/DocParser.php#L1291-L1303
        /** @var int $key */
        $key = $this->parseValue($tokenIterator);
        $tokenIterator->consumeTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_EQUAL);
        // mimics https://github.com/doctrine/annotations/blob/1.13.x/lib/Doctrine/Common/Annotations/DocParser.php#L1236-L1238
        $value = $this->parseValue($tokenIterator);
        return [
            // plain token value
            $key => $value,
        ];
    }
    /**
     * @see https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/lib/Doctrine/Common/Annotations/DocParser.php#L1051-L1079
     * @return array<mixed>
     */
    private function resolveAnnotationValues(\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator $tokenIterator) : array
    {
        $values = [];
        $resolvedValue = $this->resolveAnnotationValue($tokenIterator);
        if (\is_array($resolvedValue)) {
            $values = \array_merge($values, $resolvedValue);
        } else {
            $values[] = $resolvedValue;
        }
        while ($tokenIterator->isCurrentTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_COMMA)) {
            $tokenIterator->next();
            // if is next item just closing brackets
            if ($tokenIterator->isNextTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_CLOSE_PARENTHESES)) {
                continue;
            }
            $nestedValues = $this->resolveAnnotationValue($tokenIterator);
            if (\is_array($nestedValues)) {
                $values = \array_merge($values, $nestedValues);
            } else {
                $values[] = $nestedValues;
            }
        }
        return $values;
    }
    /**
     * @return mixed[]|\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode|\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode|string
     */
    private function parseValue(\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator $tokenIterator)
    {
        if ($tokenIterator->isCurrentTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_OPEN_CURLY_BRACKET)) {
            $items = $this->arrayParser->parseCurlyArray($tokenIterator);
            return new \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode($items);
        }
        return $this->plainValueParser->parseValue($tokenIterator);
    }
}
