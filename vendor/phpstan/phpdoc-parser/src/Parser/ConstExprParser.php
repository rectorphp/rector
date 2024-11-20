<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Parser;

use PHPStan\PhpDocParser\Ast;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\ParserConfig;
use function str_replace;
use function strtolower;
class ConstExprParser
{
    private ParserConfig $config;
    private bool $parseDoctrineStrings;
    public function __construct(ParserConfig $config)
    {
        $this->config = $config;
        $this->parseDoctrineStrings = \false;
    }
    /**
     * @internal
     */
    public function toDoctrine() : self
    {
        $self = new self($this->config);
        $self->parseDoctrineStrings = \true;
        return $self;
    }
    public function parse(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\ConstExpr\ConstExprNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_FLOAT)) {
            $value = $tokens->currentTokenValue();
            $tokens->next();
            return $this->enrichWithAttributes($tokens, new Ast\ConstExpr\ConstExprFloatNode(str_replace('_', '', $value)), $startLine, $startIndex);
        }
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_INTEGER)) {
            $value = $tokens->currentTokenValue();
            $tokens->next();
            return $this->enrichWithAttributes($tokens, new Ast\ConstExpr\ConstExprIntegerNode(str_replace('_', '', $value)), $startLine, $startIndex);
        }
        if ($this->parseDoctrineStrings && $tokens->isCurrentTokenType(Lexer::TOKEN_DOCTRINE_ANNOTATION_STRING)) {
            $value = $tokens->currentTokenValue();
            $tokens->next();
            return $this->enrichWithAttributes($tokens, new Ast\ConstExpr\DoctrineConstExprStringNode(Ast\ConstExpr\DoctrineConstExprStringNode::unescape($value)), $startLine, $startIndex);
        }
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_SINGLE_QUOTED_STRING, Lexer::TOKEN_DOUBLE_QUOTED_STRING)) {
            if ($this->parseDoctrineStrings) {
                if ($tokens->isCurrentTokenType(Lexer::TOKEN_SINGLE_QUOTED_STRING)) {
                    throw new \PHPStan\PhpDocParser\Parser\ParserException($tokens->currentTokenValue(), $tokens->currentTokenType(), $tokens->currentTokenOffset(), Lexer::TOKEN_DOUBLE_QUOTED_STRING, null, $tokens->currentTokenLine());
                }
                $value = $tokens->currentTokenValue();
                $tokens->next();
                return $this->enrichWithAttributes($tokens, $this->parseDoctrineString($value, $tokens), $startLine, $startIndex);
            }
            $value = \PHPStan\PhpDocParser\Parser\StringUnescaper::unescapeString($tokens->currentTokenValue());
            $type = $tokens->currentTokenType();
            $tokens->next();
            return $this->enrichWithAttributes($tokens, new Ast\ConstExpr\ConstExprStringNode($value, $type === Lexer::TOKEN_SINGLE_QUOTED_STRING ? Ast\ConstExpr\ConstExprStringNode::SINGLE_QUOTED : Ast\ConstExpr\ConstExprStringNode::DOUBLE_QUOTED), $startLine, $startIndex);
        } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_IDENTIFIER)) {
            $identifier = $tokens->currentTokenValue();
            $tokens->next();
            switch (strtolower($identifier)) {
                case 'true':
                    return $this->enrichWithAttributes($tokens, new Ast\ConstExpr\ConstExprTrueNode(), $startLine, $startIndex);
                case 'false':
                    return $this->enrichWithAttributes($tokens, new Ast\ConstExpr\ConstExprFalseNode(), $startLine, $startIndex);
                case 'null':
                    return $this->enrichWithAttributes($tokens, new Ast\ConstExpr\ConstExprNullNode(), $startLine, $startIndex);
                case 'array':
                    $tokens->consumeTokenType(Lexer::TOKEN_OPEN_PARENTHESES);
                    return $this->parseArray($tokens, Lexer::TOKEN_CLOSE_PARENTHESES, $startIndex);
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
                return $this->enrichWithAttributes($tokens, new Ast\ConstExpr\ConstFetchNode($identifier, $classConstantName), $startLine, $startIndex);
            }
            return $this->enrichWithAttributes($tokens, new Ast\ConstExpr\ConstFetchNode('', $identifier), $startLine, $startIndex);
        } elseif ($tokens->tryConsumeTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
            return $this->parseArray($tokens, Lexer::TOKEN_CLOSE_SQUARE_BRACKET, $startIndex);
        }
        throw new \PHPStan\PhpDocParser\Parser\ParserException($tokens->currentTokenValue(), $tokens->currentTokenType(), $tokens->currentTokenOffset(), Lexer::TOKEN_IDENTIFIER, null, $tokens->currentTokenLine());
    }
    private function parseArray(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, int $endToken, int $startIndex) : Ast\ConstExpr\ConstExprArrayNode
    {
        $items = [];
        $startLine = $tokens->currentTokenLine();
        if (!$tokens->tryConsumeTokenType($endToken)) {
            do {
                $items[] = $this->parseArrayItem($tokens);
            } while ($tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA) && !$tokens->isCurrentTokenType($endToken));
            $tokens->consumeTokenType($endToken);
        }
        return $this->enrichWithAttributes($tokens, new Ast\ConstExpr\ConstExprArrayNode($items), $startLine, $startIndex);
    }
    /**
     * This method is supposed to be called with TokenIterator after reading TOKEN_DOUBLE_QUOTED_STRING and shifting
     * to the next token.
     */
    public function parseDoctrineString(string $text, \PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\ConstExpr\DoctrineConstExprStringNode
    {
        // Because of how Lexer works, a valid Doctrine string
        // can consist of a sequence of TOKEN_DOUBLE_QUOTED_STRING and TOKEN_DOCTRINE_ANNOTATION_STRING
        while ($tokens->isCurrentTokenType(Lexer::TOKEN_DOUBLE_QUOTED_STRING, Lexer::TOKEN_DOCTRINE_ANNOTATION_STRING)) {
            $text .= $tokens->currentTokenValue();
            $tokens->next();
        }
        return new Ast\ConstExpr\DoctrineConstExprStringNode(Ast\ConstExpr\DoctrineConstExprStringNode::unescape($text));
    }
    private function parseArrayItem(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\ConstExpr\ConstExprArrayItemNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        $expr = $this->parse($tokens);
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_DOUBLE_ARROW)) {
            $key = $expr;
            $value = $this->parse($tokens);
        } else {
            $key = null;
            $value = $expr;
        }
        return $this->enrichWithAttributes($tokens, new Ast\ConstExpr\ConstExprArrayItemNode($key, $value), $startLine, $startIndex);
    }
    /**
     * @template T of Ast\ConstExpr\ConstExprNode
     * @param T $node
     * @return T
     */
    private function enrichWithAttributes(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\ConstExpr\ConstExprNode $node, int $startLine, int $startIndex) : Ast\ConstExpr\ConstExprNode
    {
        if ($this->config->useLinesAttributes) {
            $node->setAttribute(Ast\Attribute::START_LINE, $startLine);
            $node->setAttribute(Ast\Attribute::END_LINE, $tokens->currentTokenLine());
        }
        if ($this->config->useIndexAttributes) {
            $node->setAttribute(Ast\Attribute::START_INDEX, $startIndex);
            $node->setAttribute(Ast\Attribute::END_INDEX, $tokens->endIndexOfLastRelevantToken());
        }
        return $node;
    }
}
