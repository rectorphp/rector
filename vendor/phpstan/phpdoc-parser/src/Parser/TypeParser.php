<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Parser;

use LogicException;
use PHPStan\PhpDocParser\Ast;
use PHPStan\PhpDocParser\Lexer\Lexer;
use function strpos;
use function trim;
class TypeParser
{
    /** @var ConstExprParser|null */
    private $constExprParser;
    public function __construct(?\PHPStan\PhpDocParser\Parser\ConstExprParser $constExprParser = null)
    {
        $this->constExprParser = $constExprParser;
    }
    /** @phpstan-impure */
    public function parse(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\TypeNode
    {
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_NULLABLE)) {
            $type = $this->parseNullable($tokens);
        } else {
            $type = $this->parseAtomic($tokens);
            if ($tokens->isCurrentTokenType(Lexer::TOKEN_UNION)) {
                $type = $this->parseUnion($tokens, $type);
            } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_INTERSECTION)) {
                $type = $this->parseIntersection($tokens, $type);
            }
        }
        return $type;
    }
    /** @phpstan-impure */
    private function subParse(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\TypeNode
    {
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_NULLABLE)) {
            $type = $this->parseNullable($tokens);
        } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_VARIABLE)) {
            $type = $this->parseConditionalForParameter($tokens, $tokens->currentTokenValue());
        } else {
            $type = $this->parseAtomic($tokens);
            if ($tokens->isCurrentTokenValue('is')) {
                $type = $this->parseConditional($tokens, $type);
            } else {
                $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
                if ($tokens->isCurrentTokenType(Lexer::TOKEN_UNION)) {
                    $type = $this->subParseUnion($tokens, $type);
                } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_INTERSECTION)) {
                    $type = $this->subParseIntersection($tokens, $type);
                }
            }
        }
        return $type;
    }
    /** @phpstan-impure */
    private function parseAtomic(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\TypeNode
    {
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
            $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
            $type = $this->subParse($tokens);
            $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
            $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_PARENTHESES);
            if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                return $this->tryParseArrayOrOffsetAccess($tokens, $type);
            }
            return $type;
        }
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_THIS_VARIABLE)) {
            $type = new Ast\Type\ThisTypeNode();
            if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                return $this->tryParseArrayOrOffsetAccess($tokens, $type);
            }
            return $type;
        }
        $currentTokenValue = $tokens->currentTokenValue();
        $tokens->pushSavePoint();
        // because of ConstFetchNode
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_IDENTIFIER)) {
            $type = new Ast\Type\IdentifierTypeNode($currentTokenValue);
            if (!$tokens->isCurrentTokenType(Lexer::TOKEN_DOUBLE_COLON)) {
                $tokens->dropSavePoint();
                // because of ConstFetchNode
                if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_ANGLE_BRACKET)) {
                    $tokens->pushSavePoint();
                    $isHtml = $this->isHtml($tokens);
                    $tokens->rollback();
                    if ($isHtml) {
                        return $type;
                    }
                    $type = $this->parseGeneric($tokens, $type);
                    if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                        $type = $this->tryParseArrayOrOffsetAccess($tokens, $type);
                    }
                } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
                    $type = $this->tryParseCallable($tokens, $type);
                } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                    $type = $this->tryParseArrayOrOffsetAccess($tokens, $type);
                } elseif ($type->name === 'array' && $tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_CURLY_BRACKET) && !$tokens->isPrecededByHorizontalWhitespace()) {
                    $type = $this->parseArrayShape($tokens, $type);
                    if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                        $type = $this->tryParseArrayOrOffsetAccess($tokens, $type);
                    }
                }
                return $type;
            } else {
                $tokens->rollback();
                // because of ConstFetchNode
            }
        } else {
            $tokens->dropSavePoint();
            // because of ConstFetchNode
        }
        $exception = new \PHPStan\PhpDocParser\Parser\ParserException($tokens->currentTokenValue(), $tokens->currentTokenType(), $tokens->currentTokenOffset(), Lexer::TOKEN_IDENTIFIER);
        if ($this->constExprParser === null) {
            throw $exception;
        }
        try {
            $constExpr = $this->constExprParser->parse($tokens, \true);
            if ($constExpr instanceof Ast\ConstExpr\ConstExprArrayNode) {
                throw $exception;
            }
            return new Ast\Type\ConstTypeNode($constExpr);
        } catch (LogicException $e) {
            throw $exception;
        }
    }
    /** @phpstan-impure */
    private function parseUnion(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\TypeNode $type) : Ast\Type\TypeNode
    {
        $types = [$type];
        while ($tokens->tryConsumeTokenType(Lexer::TOKEN_UNION)) {
            $types[] = $this->parseAtomic($tokens);
        }
        return new Ast\Type\UnionTypeNode($types);
    }
    /** @phpstan-impure */
    private function subParseUnion(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\TypeNode $type) : Ast\Type\TypeNode
    {
        $types = [$type];
        while ($tokens->tryConsumeTokenType(Lexer::TOKEN_UNION)) {
            $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
            $types[] = $this->parseAtomic($tokens);
            $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        }
        return new Ast\Type\UnionTypeNode($types);
    }
    /** @phpstan-impure */
    private function parseIntersection(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\TypeNode $type) : Ast\Type\TypeNode
    {
        $types = [$type];
        while ($tokens->tryConsumeTokenType(Lexer::TOKEN_INTERSECTION)) {
            $types[] = $this->parseAtomic($tokens);
        }
        return new Ast\Type\IntersectionTypeNode($types);
    }
    /** @phpstan-impure */
    private function subParseIntersection(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\TypeNode $type) : Ast\Type\TypeNode
    {
        $types = [$type];
        while ($tokens->tryConsumeTokenType(Lexer::TOKEN_INTERSECTION)) {
            $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
            $types[] = $this->parseAtomic($tokens);
            $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        }
        return new Ast\Type\IntersectionTypeNode($types);
    }
    /** @phpstan-impure */
    private function parseConditional(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\TypeNode $subjectType) : Ast\Type\TypeNode
    {
        $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
        $negated = \false;
        if ($tokens->isCurrentTokenValue('not')) {
            $negated = \true;
            $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
        }
        $targetType = $this->parse($tokens);
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $tokens->consumeTokenType(Lexer::TOKEN_NULLABLE);
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $ifType = $this->parse($tokens);
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $tokens->consumeTokenType(Lexer::TOKEN_COLON);
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $elseType = $this->subParse($tokens);
        return new Ast\Type\ConditionalTypeNode($subjectType, $targetType, $ifType, $elseType, $negated);
    }
    /** @phpstan-impure */
    private function parseConditionalForParameter(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, string $parameterName) : Ast\Type\TypeNode
    {
        $tokens->consumeTokenType(Lexer::TOKEN_VARIABLE);
        $tokens->consumeTokenValue(Lexer::TOKEN_IDENTIFIER, 'is');
        $negated = \false;
        if ($tokens->isCurrentTokenValue('not')) {
            $negated = \true;
            $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
        }
        $targetType = $this->parse($tokens);
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $tokens->consumeTokenType(Lexer::TOKEN_NULLABLE);
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $ifType = $this->parse($tokens);
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $tokens->consumeTokenType(Lexer::TOKEN_COLON);
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $elseType = $this->subParse($tokens);
        return new Ast\Type\ConditionalTypeForParameterNode($parameterName, $targetType, $ifType, $elseType, $negated);
    }
    /** @phpstan-impure */
    private function parseNullable(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\TypeNode
    {
        $tokens->consumeTokenType(Lexer::TOKEN_NULLABLE);
        $type = $this->parseAtomic($tokens);
        return new Ast\Type\NullableTypeNode($type);
    }
    /** @phpstan-impure */
    public function isHtml(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : bool
    {
        $tokens->consumeTokenType(Lexer::TOKEN_OPEN_ANGLE_BRACKET);
        if (!$tokens->isCurrentTokenType(Lexer::TOKEN_IDENTIFIER)) {
            return \false;
        }
        $htmlTagName = $tokens->currentTokenValue();
        $tokens->next();
        if (!$tokens->tryConsumeTokenType(Lexer::TOKEN_CLOSE_ANGLE_BRACKET)) {
            return \false;
        }
        while (!$tokens->isCurrentTokenType(Lexer::TOKEN_END)) {
            if ($tokens->tryConsumeTokenType(Lexer::TOKEN_OPEN_ANGLE_BRACKET) && strpos($tokens->currentTokenValue(), '/' . $htmlTagName . '>') !== \false) {
                return \true;
            }
            $tokens->next();
        }
        return \false;
    }
    /** @phpstan-impure */
    public function parseGeneric(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\IdentifierTypeNode $baseType) : Ast\Type\GenericTypeNode
    {
        $tokens->consumeTokenType(Lexer::TOKEN_OPEN_ANGLE_BRACKET);
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $genericTypes = [$this->parse($tokens)];
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        while ($tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA)) {
            $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
            if ($tokens->tryConsumeTokenType(Lexer::TOKEN_CLOSE_ANGLE_BRACKET)) {
                // trailing comma case
                return new Ast\Type\GenericTypeNode($baseType, $genericTypes);
            }
            $genericTypes[] = $this->parse($tokens);
            $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        }
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_ANGLE_BRACKET);
        return new Ast\Type\GenericTypeNode($baseType, $genericTypes);
    }
    /** @phpstan-impure */
    private function parseCallable(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\IdentifierTypeNode $identifier) : Ast\Type\TypeNode
    {
        $tokens->consumeTokenType(Lexer::TOKEN_OPEN_PARENTHESES);
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $parameters = [];
        if (!$tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_PARENTHESES)) {
            $parameters[] = $this->parseCallableParameter($tokens);
            $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
            while ($tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA)) {
                $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
                if ($tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_PARENTHESES)) {
                    break;
                }
                $parameters[] = $this->parseCallableParameter($tokens);
                $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
            }
        }
        $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_PARENTHESES);
        $tokens->consumeTokenType(Lexer::TOKEN_COLON);
        $returnType = $this->parseCallableReturnType($tokens);
        return new Ast\Type\CallableTypeNode($identifier, $parameters, $returnType);
    }
    /** @phpstan-impure */
    private function parseCallableParameter(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\CallableTypeParameterNode
    {
        $type = $this->parse($tokens);
        $isReference = $tokens->tryConsumeTokenType(Lexer::TOKEN_REFERENCE);
        $isVariadic = $tokens->tryConsumeTokenType(Lexer::TOKEN_VARIADIC);
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_VARIABLE)) {
            $parameterName = $tokens->currentTokenValue();
            $tokens->consumeTokenType(Lexer::TOKEN_VARIABLE);
        } else {
            $parameterName = '';
        }
        $isOptional = $tokens->tryConsumeTokenType(Lexer::TOKEN_EQUAL);
        return new Ast\Type\CallableTypeParameterNode($type, $isReference, $isVariadic, $parameterName, $isOptional);
    }
    /** @phpstan-impure */
    private function parseCallableReturnType(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\TypeNode
    {
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_NULLABLE)) {
            $type = $this->parseNullable($tokens);
        } elseif ($tokens->tryConsumeTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
            $type = $this->parse($tokens);
            $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_PARENTHESES);
        } else {
            $type = new Ast\Type\IdentifierTypeNode($tokens->currentTokenValue());
            $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
            if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_ANGLE_BRACKET)) {
                $type = $this->parseGeneric($tokens, $type);
            } elseif ($type->name === 'array' && $tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_CURLY_BRACKET) && !$tokens->isPrecededByHorizontalWhitespace()) {
                $type = $this->parseArrayShape($tokens, $type);
            }
        }
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
            $type = $this->tryParseArrayOrOffsetAccess($tokens, $type);
        }
        return $type;
    }
    /** @phpstan-impure */
    private function tryParseCallable(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\IdentifierTypeNode $identifier) : Ast\Type\TypeNode
    {
        try {
            $tokens->pushSavePoint();
            $type = $this->parseCallable($tokens, $identifier);
            $tokens->dropSavePoint();
        } catch (\PHPStan\PhpDocParser\Parser\ParserException $e) {
            $tokens->rollback();
            $type = $identifier;
        }
        return $type;
    }
    /** @phpstan-impure */
    private function tryParseArrayOrOffsetAccess(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\TypeNode $type) : Ast\Type\TypeNode
    {
        try {
            while ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                $tokens->pushSavePoint();
                $canBeOffsetAccessType = !$tokens->isPrecededByHorizontalWhitespace();
                $tokens->consumeTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET);
                if ($canBeOffsetAccessType && !$tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_SQUARE_BRACKET)) {
                    $offset = $this->parse($tokens);
                    $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_SQUARE_BRACKET);
                    $tokens->dropSavePoint();
                    $type = new Ast\Type\OffsetAccessTypeNode($type, $offset);
                } else {
                    $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_SQUARE_BRACKET);
                    $tokens->dropSavePoint();
                    $type = new Ast\Type\ArrayTypeNode($type);
                }
            }
        } catch (\PHPStan\PhpDocParser\Parser\ParserException $e) {
            $tokens->rollback();
        }
        return $type;
    }
    /** @phpstan-impure */
    private function parseArrayShape(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\TypeNode $type) : Ast\Type\ArrayShapeNode
    {
        $tokens->consumeTokenType(Lexer::TOKEN_OPEN_CURLY_BRACKET);
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_CLOSE_CURLY_BRACKET)) {
            return new Ast\Type\ArrayShapeNode([]);
        }
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $items = [$this->parseArrayShapeItem($tokens)];
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        while ($tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA)) {
            $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
            if ($tokens->tryConsumeTokenType(Lexer::TOKEN_CLOSE_CURLY_BRACKET)) {
                // trailing comma case
                return new Ast\Type\ArrayShapeNode($items);
            }
            $items[] = $this->parseArrayShapeItem($tokens);
            $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        }
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_CURLY_BRACKET);
        return new Ast\Type\ArrayShapeNode($items);
    }
    /** @phpstan-impure */
    private function parseArrayShapeItem(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\ArrayShapeItemNode
    {
        try {
            $tokens->pushSavePoint();
            $key = $this->parseArrayShapeKey($tokens);
            $optional = $tokens->tryConsumeTokenType(Lexer::TOKEN_NULLABLE);
            $tokens->consumeTokenType(Lexer::TOKEN_COLON);
            $value = $this->parse($tokens);
            $tokens->dropSavePoint();
            return new Ast\Type\ArrayShapeItemNode($key, $optional, $value);
        } catch (\PHPStan\PhpDocParser\Parser\ParserException $e) {
            $tokens->rollback();
            $value = $this->parse($tokens);
            return new Ast\Type\ArrayShapeItemNode(null, \false, $value);
        }
    }
    /**
     * @phpstan-impure
     * @return Ast\ConstExpr\ConstExprIntegerNode|Ast\ConstExpr\ConstExprStringNode|Ast\Type\IdentifierTypeNode
     */
    private function parseArrayShapeKey(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens)
    {
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_INTEGER)) {
            $key = new Ast\ConstExpr\ConstExprIntegerNode($tokens->currentTokenValue());
            $tokens->next();
        } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_SINGLE_QUOTED_STRING)) {
            $key = new Ast\ConstExpr\ConstExprStringNode(trim($tokens->currentTokenValue(), "'"));
            $tokens->next();
        } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_DOUBLE_QUOTED_STRING)) {
            $key = new Ast\ConstExpr\ConstExprStringNode(trim($tokens->currentTokenValue(), '"'));
            $tokens->next();
        } else {
            $key = new Ast\Type\IdentifierTypeNode($tokens->currentTokenValue());
            $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
        }
        return $key;
    }
}
