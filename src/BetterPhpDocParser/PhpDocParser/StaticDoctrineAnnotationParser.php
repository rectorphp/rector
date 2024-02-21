<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\PlainValueParser;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Util\NewLineSplitter;
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
    /**
     * @var string
     * @see https://regex101.com/r/Pthg5d/1
     */
    private const END_OF_VALUE_CHARACTERS_REGEX = '/^[)} \\r\\n"\']+$/i';
    public function __construct(PlainValueParser $plainValueParser, ArrayParser $arrayParser)
    {
        $this->plainValueParser = $plainValueParser;
        $this->arrayParser = $arrayParser;
    }
    /**
     * mimics: https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/lib/Doctrine/Common/Annotations/DocParser.php#L1024-L1041
     *
     * @return ArrayItemNode[]
     */
    public function resolveAnnotationMethodCall(BetterTokenIterator $tokenIterator, Node $currentPhpNode) : array
    {
        if (!$tokenIterator->isCurrentTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
            return [];
        }
        $tokenIterator->consumeTokenType(Lexer::TOKEN_OPEN_PARENTHESES);
        // empty ()
        if ($tokenIterator->isCurrentTokenType(Lexer::TOKEN_CLOSE_PARENTHESES)) {
            return [];
        }
        return $this->resolveAnnotationValues($tokenIterator, $currentPhpNode);
    }
    /**
     * @api tests
     * @see https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/lib/Doctrine/Common/Annotations/DocParser.php#L1215-L1224
     * @return CurlyListNode|string|array<mixed>|ConstExprNode|DoctrineAnnotationTagValueNode|StringNode
     */
    public function resolveAnnotationValue(BetterTokenIterator $tokenIterator, Node $currentPhpNode)
    {
        // skips dummy tokens like newlines
        $tokenIterator->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        // no assign
        if (!$tokenIterator->isNextTokenType(Lexer::TOKEN_EQUAL)) {
            // 1. plain value - mimics https://github.com/doctrine/annotations/blob/0cb0cd2950a5c6cdbf22adbe2bfd5fd1ea68588f/lib/Doctrine/Common/Annotations/DocParser.php#L1234-L1282
            return $this->parseValue($tokenIterator, $currentPhpNode);
        }
        // 2. assign key = value - mimics FieldAssignment() https://github.com/doctrine/annotations/blob/0cb0cd2950a5c6cdbf22adbe2bfd5fd1ea68588f/lib/Doctrine/Common/Annotations/DocParser.php#L1291-L1303
        /** @var int $key */
        $key = $this->parseValue($tokenIterator, $currentPhpNode);
        $tokenIterator->consumeTokenType(Lexer::TOKEN_EQUAL);
        // mimics https://github.com/doctrine/annotations/blob/1.13.x/lib/Doctrine/Common/Annotations/DocParser.php#L1236-L1238
        $value = $this->parseValue($tokenIterator, $currentPhpNode);
        return [
            // plain token value
            $key => $value,
        ];
    }
    public function getCommentFromRestOfAnnotation(BetterTokenIterator $tokenIterator, string $annotationContent) : string
    {
        // we skip all the remaining tokens from the end of the declaration of values
        while (\preg_match(self::END_OF_VALUE_CHARACTERS_REGEX, $tokenIterator->currentTokenValue())) {
            $tokenIterator->next();
        }
        // the remaining of the annotation content is the comment
        $comment = \substr($annotationContent, $tokenIterator->currentTokenOffset());
        // we only keep the first line as this will be added as a line comment at the end of the attribute
        $commentLines = NewLineSplitter::split($comment);
        return $commentLines[0];
    }
    /**
     * @see https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/lib/Doctrine/Common/Annotations/DocParser.php#L1051-L1079
     *
     * @return ArrayItemNode[]
     */
    private function resolveAnnotationValues(BetterTokenIterator $tokenIterator, Node $currentPhpNode) : array
    {
        $values = [];
        $resolvedValue = $this->resolveAnnotationValue($tokenIterator, $currentPhpNode);
        if (\is_array($resolvedValue)) {
            $values = \array_merge($values, $resolvedValue);
        } else {
            $values[] = $resolvedValue;
        }
        while ($tokenIterator->isCurrentTokenType(Lexer::TOKEN_COMMA)) {
            $tokenIterator->next();
            // if is next item just closing brackets
            if ($tokenIterator->isNextTokenType(Lexer::TOKEN_CLOSE_PARENTHESES)) {
                continue;
            }
            $nestedValues = $this->resolveAnnotationValue($tokenIterator, $currentPhpNode);
            if (\is_array($nestedValues)) {
                $values = \array_merge($values, $nestedValues);
            } else {
                if ($tokenIterator->isCurrentTokenType(Lexer::TOKEN_END)) {
                    break;
                }
                $values[] = $nestedValues;
            }
        }
        return $this->arrayParser->createArrayFromValues($values);
    }
    /**
     * @return CurlyListNode|string|array<mixed>|ConstExprNode|DoctrineAnnotationTagValueNode|StringNode
     */
    private function parseValue(BetterTokenIterator $tokenIterator, Node $currentPhpNode)
    {
        if ($tokenIterator->isCurrentTokenType(Lexer::TOKEN_OPEN_CURLY_BRACKET)) {
            $items = $this->arrayParser->parseCurlyArray($tokenIterator, $currentPhpNode);
            return new CurlyListNode($items);
        }
        return $this->plainValueParser->parseValue($tokenIterator, $currentPhpNode);
    }
}
