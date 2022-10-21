<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Parser;

use PHPStan\PhpDocParser\Ast;
use PHPStan\PhpDocParser\Lexer\Lexer;
use function chr;
use function hexdec;
use function octdec;
use function preg_replace_callback;
use function str_replace;
use function strtolower;
use function substr;
class ConstExprParser
{
    private const REPLACEMENTS = ['\\' => '\\', 'n' => "\n", 'r' => "\r", 't' => "\t", 'f' => "\f", 'v' => "\v", 'e' => "\x1b"];
    /** @var bool */
    private $unescapeStrings;
    public function __construct(bool $unescapeStrings = \false)
    {
        $this->unescapeStrings = $unescapeStrings;
    }
    public function parse(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, bool $trimStrings = \false) : Ast\ConstExpr\ConstExprNode
    {
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_FLOAT)) {
            $value = $tokens->currentTokenValue();
            $tokens->next();
            return new Ast\ConstExpr\ConstExprFloatNode($value);
        }
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_INTEGER)) {
            $value = $tokens->currentTokenValue();
            $tokens->next();
            return new Ast\ConstExpr\ConstExprIntegerNode($value);
        }
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_SINGLE_QUOTED_STRING, Lexer::TOKEN_DOUBLE_QUOTED_STRING)) {
            $value = $tokens->currentTokenValue();
            if ($trimStrings) {
                if ($this->unescapeStrings) {
                    $value = self::unescapeString($value);
                } else {
                    $value = substr($value, 1, -1);
                }
            }
            $tokens->next();
            return new Ast\ConstExpr\ConstExprStringNode($value);
        } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_IDENTIFIER)) {
            $identifier = $tokens->currentTokenValue();
            $tokens->next();
            switch (strtolower($identifier)) {
                case 'true':
                    return new Ast\ConstExpr\ConstExprTrueNode();
                case 'false':
                    return new Ast\ConstExpr\ConstExprFalseNode();
                case 'null':
                    return new Ast\ConstExpr\ConstExprNullNode();
                case 'array':
                    $tokens->consumeTokenType(Lexer::TOKEN_OPEN_PARENTHESES);
                    return $this->parseArray($tokens, Lexer::TOKEN_CLOSE_PARENTHESES);
            }
            if ($tokens->tryConsumeTokenType(Lexer::TOKEN_DOUBLE_COLON)) {
                $classConstantName = '';
                $lastType = null;
                while (\true) {
                    if ($lastType !== Lexer::TOKEN_IDENTIFIER && $tokens->currentTokenType() === Lexer::TOKEN_IDENTIFIER) {
                        $classConstantName .= $tokens->currentTokenValue();
                        $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
                        $lastType = Lexer::TOKEN_IDENTIFIER;
                        continue;
                    }
                    if ($lastType !== Lexer::TOKEN_WILDCARD && $tokens->tryConsumeTokenType(Lexer::TOKEN_WILDCARD)) {
                        $classConstantName .= '*';
                        $lastType = Lexer::TOKEN_WILDCARD;
                        if ($tokens->getSkippedHorizontalWhiteSpaceIfAny() !== '') {
                            break;
                        }
                        continue;
                    }
                    if ($lastType === null) {
                        // trigger parse error if nothing valid was consumed
                        $tokens->consumeTokenType(Lexer::TOKEN_WILDCARD);
                    }
                    break;
                }
                return new Ast\ConstExpr\ConstFetchNode($identifier, $classConstantName);
            }
            return new Ast\ConstExpr\ConstFetchNode('', $identifier);
        } elseif ($tokens->tryConsumeTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
            return $this->parseArray($tokens, Lexer::TOKEN_CLOSE_SQUARE_BRACKET);
        }
        throw new \PHPStan\PhpDocParser\Parser\ParserException($tokens->currentTokenValue(), $tokens->currentTokenType(), $tokens->currentTokenOffset(), Lexer::TOKEN_IDENTIFIER);
    }
    private function parseArray(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, int $endToken) : Ast\ConstExpr\ConstExprArrayNode
    {
        $items = [];
        if (!$tokens->tryConsumeTokenType($endToken)) {
            do {
                $items[] = $this->parseArrayItem($tokens);
            } while ($tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA) && !$tokens->isCurrentTokenType($endToken));
            $tokens->consumeTokenType($endToken);
        }
        return new Ast\ConstExpr\ConstExprArrayNode($items);
    }
    private function parseArrayItem(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\ConstExpr\ConstExprArrayItemNode
    {
        $expr = $this->parse($tokens);
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_DOUBLE_ARROW)) {
            $key = $expr;
            $value = $this->parse($tokens);
        } else {
            $key = null;
            $value = $expr;
        }
        return new Ast\ConstExpr\ConstExprArrayItemNode($key, $value);
    }
    private static function unescapeString(string $string) : string
    {
        $quote = $string[0];
        if ($quote === '\'') {
            return str_replace(['\\\\', '\\\''], ['\\', '\''], substr($string, 1, -1));
        }
        return self::parseEscapeSequences(substr($string, 1, -1), '"');
    }
    /**
     * Implementation based on https://github.com/nikic/PHP-Parser/blob/b0edd4c41111042d43bb45c6c657b2e0db367d9e/lib/PhpParser/Node/Scalar/String_.php#L90-L130
     */
    private static function parseEscapeSequences(string $str, string $quote) : string
    {
        $str = str_replace('\\' . $quote, $quote, $str);
        return preg_replace_callback('~\\\\([\\\\nrtfve]|[xX][0-9a-fA-F]{1,2}|[0-7]{1,3}|u\\{([0-9a-fA-F]+)\\})~', static function ($matches) {
            $str = $matches[1];
            if (isset(self::REPLACEMENTS[$str])) {
                return self::REPLACEMENTS[$str];
            }
            if ($str[0] === 'x' || $str[0] === 'X') {
                return chr(hexdec(substr($str, 1)));
            }
            if ($str[0] === 'u') {
                return self::codePointToUtf8(hexdec($matches[2]));
            }
            return chr(octdec($str));
        }, $str);
    }
    /**
     * Implementation based on https://github.com/nikic/PHP-Parser/blob/b0edd4c41111042d43bb45c6c657b2e0db367d9e/lib/PhpParser/Node/Scalar/String_.php#L132-L154
     */
    private static function codePointToUtf8(int $num) : string
    {
        if ($num <= 0x7f) {
            return chr($num);
        }
        if ($num <= 0x7ff) {
            return chr(($num >> 6) + 0xc0) . chr(($num & 0x3f) + 0x80);
        }
        if ($num <= 0xffff) {
            return chr(($num >> 12) + 0xe0) . chr(($num >> 6 & 0x3f) + 0x80) . chr(($num & 0x3f) + 0x80);
        }
        if ($num <= 0x1fffff) {
            return chr(($num >> 18) + 0xf0) . chr(($num >> 12 & 0x3f) + 0x80) . chr(($num >> 6 & 0x3f) + 0x80) . chr(($num & 0x3f) + 0x80);
        }
        // Invalid UTF-8 codepoint escape sequence: Codepoint too large
        return "�";
    }
}
