<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Parser;

use LogicException;
use PHPStan\PhpDocParser\Ast;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\ParserConfig;
use function in_array;
use function str_replace;
use function strlen;
use function strpos;
use function substr_compare;
class TypeParser
{
    private ParserConfig $config;
    private \PHPStan\PhpDocParser\Parser\ConstExprParser $constExprParser;
    public function __construct(ParserConfig $config, \PHPStan\PhpDocParser\Parser\ConstExprParser $constExprParser)
    {
        $this->config = $config;
        $this->constExprParser = $constExprParser;
    }
    /** @phpstan-impure */
    public function parse(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\TypeNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_NULLABLE)) {
            $type = $this->parseNullable($tokens);
        } else {
            $type = $this->parseAtomic($tokens);
            $tokens->pushSavePoint();
            $tokens->skipNewLineTokensAndConsumeComments();
            try {
                $enrichedType = $this->enrichTypeOnUnionOrIntersection($tokens, $type);
            } catch (\PHPStan\PhpDocParser\Parser\ParserException $parserException) {
                $enrichedType = null;
            }
            if ($enrichedType !== null) {
                $type = $enrichedType;
                $tokens->dropSavePoint();
            } else {
                $tokens->rollback();
                $type = $this->enrichTypeOnUnionOrIntersection($tokens, $type) ?? $type;
            }
        }
        return $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex);
    }
    /** @phpstan-impure */
    private function enrichTypeOnUnionOrIntersection(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\TypeNode $type) : ?Ast\Type\TypeNode
    {
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_UNION)) {
            return $this->parseUnion($tokens, $type);
        }
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_INTERSECTION)) {
            return $this->parseIntersection($tokens, $type);
        }
        return null;
    }
    /**
     * @internal
     * @template T of Ast\Node
     * @param T $type
     * @return T
     */
    public function enrichWithAttributes(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Node $type, int $startLine, int $startIndex) : Ast\Node
    {
        if ($this->config->useLinesAttributes) {
            $type->setAttribute(Ast\Attribute::START_LINE, $startLine);
            $type->setAttribute(Ast\Attribute::END_LINE, $tokens->currentTokenLine());
        }
        $comments = $tokens->flushComments();
        if ($this->config->useCommentsAttributes) {
            $type->setAttribute(Ast\Attribute::COMMENTS, $comments);
        }
        if ($this->config->useIndexAttributes) {
            $type->setAttribute(Ast\Attribute::START_INDEX, $startIndex);
            $type->setAttribute(Ast\Attribute::END_INDEX, $tokens->endIndexOfLastRelevantToken());
        }
        return $type;
    }
    /** @phpstan-impure */
    private function subParse(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\TypeNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_NULLABLE)) {
            $type = $this->parseNullable($tokens);
        } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_VARIABLE)) {
            $type = $this->parseConditionalForParameter($tokens, $tokens->currentTokenValue());
        } else {
            $type = $this->parseAtomic($tokens);
            if ($tokens->isCurrentTokenValue('is')) {
                $type = $this->parseConditional($tokens, $type);
            } else {
                $tokens->skipNewLineTokensAndConsumeComments();
                if ($tokens->isCurrentTokenType(Lexer::TOKEN_UNION)) {
                    $type = $this->subParseUnion($tokens, $type);
                } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_INTERSECTION)) {
                    $type = $this->subParseIntersection($tokens, $type);
                }
            }
        }
        return $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex);
    }
    /** @phpstan-impure */
    private function parseAtomic(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\TypeNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
            $tokens->skipNewLineTokensAndConsumeComments();
            $type = $this->subParse($tokens);
            $tokens->skipNewLineTokensAndConsumeComments();
            $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_PARENTHESES);
            if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                $type = $this->tryParseArrayOrOffsetAccess($tokens, $type);
            }
            return $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex);
        }
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_THIS_VARIABLE)) {
            $type = $this->enrichWithAttributes($tokens, new Ast\Type\ThisTypeNode(), $startLine, $startIndex);
            if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                $type = $this->tryParseArrayOrOffsetAccess($tokens, $type);
            }
            return $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex);
        }
        $currentTokenValue = $tokens->currentTokenValue();
        $tokens->pushSavePoint();
        // because of ConstFetchNode
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_IDENTIFIER)) {
            $type = $this->enrichWithAttributes($tokens, new Ast\Type\IdentifierTypeNode($currentTokenValue), $startLine, $startIndex);
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
                    $origType = $type;
                    $type = $this->tryParseCallable($tokens, $type, \true);
                    if ($type === $origType) {
                        $type = $this->parseGeneric($tokens, $type);
                        if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                            $type = $this->tryParseArrayOrOffsetAccess($tokens, $type);
                        }
                    }
                } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
                    $type = $this->tryParseCallable($tokens, $type, \false);
                } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                    $type = $this->tryParseArrayOrOffsetAccess($tokens, $type);
                } elseif (in_array($type->name, [Ast\Type\ArrayShapeNode::KIND_ARRAY, Ast\Type\ArrayShapeNode::KIND_LIST, Ast\Type\ArrayShapeNode::KIND_NON_EMPTY_ARRAY, Ast\Type\ArrayShapeNode::KIND_NON_EMPTY_LIST, 'object'], \true) && $tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_CURLY_BRACKET) && !$tokens->isPrecededByHorizontalWhitespace()) {
                    if ($type->name === 'object') {
                        $type = $this->parseObjectShape($tokens);
                    } else {
                        $type = $this->parseArrayShape($tokens, $type, $type->name);
                    }
                    if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                        $type = $this->tryParseArrayOrOffsetAccess($tokens, $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex));
                    }
                }
                return $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex);
            } else {
                $tokens->rollback();
                // because of ConstFetchNode
            }
        } else {
            $tokens->dropSavePoint();
            // because of ConstFetchNode
        }
        $currentTokenValue = $tokens->currentTokenValue();
        $currentTokenType = $tokens->currentTokenType();
        $currentTokenOffset = $tokens->currentTokenOffset();
        $currentTokenLine = $tokens->currentTokenLine();
        try {
            $constExpr = $this->constExprParser->parse($tokens);
            if ($constExpr instanceof Ast\ConstExpr\ConstExprArrayNode) {
                throw new \PHPStan\PhpDocParser\Parser\ParserException($currentTokenValue, $currentTokenType, $currentTokenOffset, Lexer::TOKEN_IDENTIFIER, null, $currentTokenLine);
            }
            $type = $this->enrichWithAttributes($tokens, new Ast\Type\ConstTypeNode($constExpr), $startLine, $startIndex);
            if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                $type = $this->tryParseArrayOrOffsetAccess($tokens, $type);
            }
            return $type;
        } catch (LogicException $e) {
            throw new \PHPStan\PhpDocParser\Parser\ParserException($currentTokenValue, $currentTokenType, $currentTokenOffset, Lexer::TOKEN_IDENTIFIER, null, $currentTokenLine);
        }
    }
    /** @phpstan-impure */
    private function parseUnion(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\TypeNode $type) : Ast\Type\TypeNode
    {
        $types = [$type];
        while ($tokens->tryConsumeTokenType(Lexer::TOKEN_UNION)) {
            $types[] = $this->parseAtomic($tokens);
            $tokens->pushSavePoint();
            $tokens->skipNewLineTokensAndConsumeComments();
            if (!$tokens->isCurrentTokenType(Lexer::TOKEN_UNION)) {
                $tokens->rollback();
                break;
            }
            $tokens->dropSavePoint();
        }
        return new Ast\Type\UnionTypeNode($types);
    }
    /** @phpstan-impure */
    private function subParseUnion(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\TypeNode $type) : Ast\Type\TypeNode
    {
        $types = [$type];
        while ($tokens->tryConsumeTokenType(Lexer::TOKEN_UNION)) {
            $tokens->skipNewLineTokensAndConsumeComments();
            $types[] = $this->parseAtomic($tokens);
            $tokens->skipNewLineTokensAndConsumeComments();
        }
        return new Ast\Type\UnionTypeNode($types);
    }
    /** @phpstan-impure */
    private function parseIntersection(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\TypeNode $type) : Ast\Type\TypeNode
    {
        $types = [$type];
        while ($tokens->tryConsumeTokenType(Lexer::TOKEN_INTERSECTION)) {
            $types[] = $this->parseAtomic($tokens);
            $tokens->pushSavePoint();
            $tokens->skipNewLineTokensAndConsumeComments();
            if (!$tokens->isCurrentTokenType(Lexer::TOKEN_INTERSECTION)) {
                $tokens->rollback();
                break;
            }
            $tokens->dropSavePoint();
        }
        return new Ast\Type\IntersectionTypeNode($types);
    }
    /** @phpstan-impure */
    private function subParseIntersection(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\TypeNode $type) : Ast\Type\TypeNode
    {
        $types = [$type];
        while ($tokens->tryConsumeTokenType(Lexer::TOKEN_INTERSECTION)) {
            $tokens->skipNewLineTokensAndConsumeComments();
            $types[] = $this->parseAtomic($tokens);
            $tokens->skipNewLineTokensAndConsumeComments();
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
        $tokens->skipNewLineTokensAndConsumeComments();
        $tokens->consumeTokenType(Lexer::TOKEN_NULLABLE);
        $tokens->skipNewLineTokensAndConsumeComments();
        $ifType = $this->parse($tokens);
        $tokens->skipNewLineTokensAndConsumeComments();
        $tokens->consumeTokenType(Lexer::TOKEN_COLON);
        $tokens->skipNewLineTokensAndConsumeComments();
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
        $tokens->skipNewLineTokensAndConsumeComments();
        $tokens->consumeTokenType(Lexer::TOKEN_NULLABLE);
        $tokens->skipNewLineTokensAndConsumeComments();
        $ifType = $this->parse($tokens);
        $tokens->skipNewLineTokensAndConsumeComments();
        $tokens->consumeTokenType(Lexer::TOKEN_COLON);
        $tokens->skipNewLineTokensAndConsumeComments();
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
        $endTag = '</' . $htmlTagName . '>';
        $endTagSearchOffset = -strlen($endTag);
        while (!$tokens->isCurrentTokenType(Lexer::TOKEN_END)) {
            if ($tokens->tryConsumeTokenType(Lexer::TOKEN_OPEN_ANGLE_BRACKET) && strpos($tokens->currentTokenValue(), '/' . $htmlTagName . '>') !== \false || substr_compare($tokens->currentTokenValue(), $endTag, $endTagSearchOffset) === 0) {
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
        $tokens->skipNewLineTokensAndConsumeComments();
        $startLine = $baseType->getAttribute(Ast\Attribute::START_LINE);
        $startIndex = $baseType->getAttribute(Ast\Attribute::START_INDEX);
        $genericTypes = [];
        $variances = [];
        $isFirst = \true;
        while ($isFirst || $tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA)) {
            $tokens->skipNewLineTokensAndConsumeComments();
            // trailing comma case
            if (!$isFirst && $tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_ANGLE_BRACKET)) {
                break;
            }
            $isFirst = \false;
            [$genericTypes[], $variances[]] = $this->parseGenericTypeArgument($tokens);
            $tokens->skipNewLineTokensAndConsumeComments();
        }
        $type = new Ast\Type\GenericTypeNode($baseType, $genericTypes, $variances);
        if ($startLine !== null && $startIndex !== null) {
            $type = $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex);
        }
        $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_ANGLE_BRACKET);
        return $type;
    }
    /**
     * @phpstan-impure
     * @return array{Ast\Type\TypeNode, Ast\Type\GenericTypeNode::VARIANCE_*}
     */
    public function parseGenericTypeArgument(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : array
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_WILDCARD)) {
            return [$this->enrichWithAttributes($tokens, new Ast\Type\IdentifierTypeNode('mixed'), $startLine, $startIndex), Ast\Type\GenericTypeNode::VARIANCE_BIVARIANT];
        }
        if ($tokens->tryConsumeTokenValue('contravariant')) {
            $variance = Ast\Type\GenericTypeNode::VARIANCE_CONTRAVARIANT;
        } elseif ($tokens->tryConsumeTokenValue('covariant')) {
            $variance = Ast\Type\GenericTypeNode::VARIANCE_COVARIANT;
        } else {
            $variance = Ast\Type\GenericTypeNode::VARIANCE_INVARIANT;
        }
        $type = $this->parse($tokens);
        return [$type, $variance];
    }
    /**
     * @throws ParserException
     * @param ?callable(TokenIterator): string $parseDescription
     */
    public function parseTemplateTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, ?callable $parseDescription = null) : TemplateTagValueNode
    {
        $name = $tokens->currentTokenValue();
        $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
        $upperBound = $lowerBound = null;
        if ($tokens->tryConsumeTokenValue('of') || $tokens->tryConsumeTokenValue('as')) {
            $upperBound = $this->parse($tokens);
        }
        if ($tokens->tryConsumeTokenValue('super')) {
            $lowerBound = $this->parse($tokens);
        }
        if ($tokens->tryConsumeTokenValue('=')) {
            $default = $this->parse($tokens);
        } else {
            $default = null;
        }
        if ($parseDescription !== null) {
            $description = $parseDescription($tokens);
        } else {
            $description = '';
        }
        if ($name === '') {
            throw new LogicException('Template tag name cannot be empty.');
        }
        return new Ast\PhpDoc\TemplateTagValueNode($name, $upperBound, $description, $default, $lowerBound);
    }
    /** @phpstan-impure */
    private function parseCallable(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\IdentifierTypeNode $identifier, bool $hasTemplate) : Ast\Type\TypeNode
    {
        $templates = $hasTemplate ? $this->parseCallableTemplates($tokens) : [];
        $tokens->consumeTokenType(Lexer::TOKEN_OPEN_PARENTHESES);
        $tokens->skipNewLineTokensAndConsumeComments();
        $parameters = [];
        if (!$tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_PARENTHESES)) {
            $parameters[] = $this->parseCallableParameter($tokens);
            $tokens->skipNewLineTokensAndConsumeComments();
            while ($tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA)) {
                $tokens->skipNewLineTokensAndConsumeComments();
                if ($tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_PARENTHESES)) {
                    break;
                }
                $parameters[] = $this->parseCallableParameter($tokens);
                $tokens->skipNewLineTokensAndConsumeComments();
            }
        }
        $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_PARENTHESES);
        $tokens->consumeTokenType(Lexer::TOKEN_COLON);
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        $returnType = $this->enrichWithAttributes($tokens, $this->parseCallableReturnType($tokens), $startLine, $startIndex);
        return new Ast\Type\CallableTypeNode($identifier, $parameters, $returnType, $templates);
    }
    /**
     * @return Ast\PhpDoc\TemplateTagValueNode[]
     *
     * @phpstan-impure
     */
    private function parseCallableTemplates(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : array
    {
        $tokens->consumeTokenType(Lexer::TOKEN_OPEN_ANGLE_BRACKET);
        $templates = [];
        $isFirst = \true;
        while ($isFirst || $tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA)) {
            $tokens->skipNewLineTokensAndConsumeComments();
            // trailing comma case
            if (!$isFirst && $tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_ANGLE_BRACKET)) {
                break;
            }
            $isFirst = \false;
            $templates[] = $this->parseCallableTemplateArgument($tokens);
            $tokens->skipNewLineTokensAndConsumeComments();
        }
        $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_ANGLE_BRACKET);
        return $templates;
    }
    private function parseCallableTemplateArgument(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\TemplateTagValueNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        return $this->enrichWithAttributes($tokens, $this->parseTemplateTagValue($tokens), $startLine, $startIndex);
    }
    /** @phpstan-impure */
    private function parseCallableParameter(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\CallableTypeParameterNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
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
        return $this->enrichWithAttributes($tokens, new Ast\Type\CallableTypeParameterNode($type, $isReference, $isVariadic, $parameterName, $isOptional), $startLine, $startIndex);
    }
    /** @phpstan-impure */
    private function parseCallableReturnType(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\TypeNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_NULLABLE)) {
            return $this->parseNullable($tokens);
        } elseif ($tokens->tryConsumeTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
            $type = $this->subParse($tokens);
            $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_PARENTHESES);
            if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                $type = $this->tryParseArrayOrOffsetAccess($tokens, $type);
            }
            return $type;
        } elseif ($tokens->tryConsumeTokenType(Lexer::TOKEN_THIS_VARIABLE)) {
            $type = new Ast\Type\ThisTypeNode();
            if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                $type = $this->tryParseArrayOrOffsetAccess($tokens, $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex));
            }
            return $type;
        } else {
            $currentTokenValue = $tokens->currentTokenValue();
            $tokens->pushSavePoint();
            // because of ConstFetchNode
            if ($tokens->tryConsumeTokenType(Lexer::TOKEN_IDENTIFIER)) {
                $type = new Ast\Type\IdentifierTypeNode($currentTokenValue);
                if (!$tokens->isCurrentTokenType(Lexer::TOKEN_DOUBLE_COLON)) {
                    if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_ANGLE_BRACKET)) {
                        $type = $this->parseGeneric($tokens, $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex));
                        if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                            $type = $this->tryParseArrayOrOffsetAccess($tokens, $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex));
                        }
                    } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                        $type = $this->tryParseArrayOrOffsetAccess($tokens, $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex));
                    } elseif (in_array($type->name, [Ast\Type\ArrayShapeNode::KIND_ARRAY, Ast\Type\ArrayShapeNode::KIND_LIST, Ast\Type\ArrayShapeNode::KIND_NON_EMPTY_ARRAY, Ast\Type\ArrayShapeNode::KIND_NON_EMPTY_LIST, 'object'], \true) && $tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_CURLY_BRACKET) && !$tokens->isPrecededByHorizontalWhitespace()) {
                        if ($type->name === 'object') {
                            $type = $this->parseObjectShape($tokens);
                        } else {
                            $type = $this->parseArrayShape($tokens, $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex), $type->name);
                        }
                        if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                            $type = $this->tryParseArrayOrOffsetAccess($tokens, $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex));
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
        }
        $currentTokenValue = $tokens->currentTokenValue();
        $currentTokenType = $tokens->currentTokenType();
        $currentTokenOffset = $tokens->currentTokenOffset();
        $currentTokenLine = $tokens->currentTokenLine();
        try {
            $constExpr = $this->constExprParser->parse($tokens);
            if ($constExpr instanceof Ast\ConstExpr\ConstExprArrayNode) {
                throw new \PHPStan\PhpDocParser\Parser\ParserException($currentTokenValue, $currentTokenType, $currentTokenOffset, Lexer::TOKEN_IDENTIFIER, null, $currentTokenLine);
            }
            $type = $this->enrichWithAttributes($tokens, new Ast\Type\ConstTypeNode($constExpr), $startLine, $startIndex);
            if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_SQUARE_BRACKET)) {
                $type = $this->tryParseArrayOrOffsetAccess($tokens, $type);
            }
            return $type;
        } catch (LogicException $e) {
            throw new \PHPStan\PhpDocParser\Parser\ParserException($currentTokenValue, $currentTokenType, $currentTokenOffset, Lexer::TOKEN_IDENTIFIER, null, $currentTokenLine);
        }
    }
    /** @phpstan-impure */
    private function tryParseCallable(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\IdentifierTypeNode $identifier, bool $hasTemplate) : Ast\Type\TypeNode
    {
        try {
            $tokens->pushSavePoint();
            $type = $this->parseCallable($tokens, $identifier, $hasTemplate);
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
        $startLine = $type->getAttribute(Ast\Attribute::START_LINE);
        $startIndex = $type->getAttribute(Ast\Attribute::START_INDEX);
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
                    if ($startLine !== null && $startIndex !== null) {
                        $type = $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex);
                    }
                } else {
                    $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_SQUARE_BRACKET);
                    $tokens->dropSavePoint();
                    $type = new Ast\Type\ArrayTypeNode($type);
                    if ($startLine !== null && $startIndex !== null) {
                        $type = $this->enrichWithAttributes($tokens, $type, $startLine, $startIndex);
                    }
                }
            }
        } catch (\PHPStan\PhpDocParser\Parser\ParserException $e) {
            $tokens->rollback();
        }
        return $type;
    }
    /**
     * @phpstan-impure
     * @param Ast\Type\ArrayShapeNode::KIND_* $kind
     */
    private function parseArrayShape(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Type\TypeNode $type, string $kind) : Ast\Type\ArrayShapeNode
    {
        $tokens->consumeTokenType(Lexer::TOKEN_OPEN_CURLY_BRACKET);
        $items = [];
        $sealed = \true;
        $unsealedType = null;
        $done = \false;
        do {
            $tokens->skipNewLineTokensAndConsumeComments();
            if ($tokens->tryConsumeTokenType(Lexer::TOKEN_CLOSE_CURLY_BRACKET)) {
                return Ast\Type\ArrayShapeNode::createSealed($items, $kind);
            }
            if ($tokens->tryConsumeTokenType(Lexer::TOKEN_VARIADIC)) {
                $sealed = \false;
                $tokens->skipNewLineTokensAndConsumeComments();
                if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_ANGLE_BRACKET)) {
                    if ($kind === Ast\Type\ArrayShapeNode::KIND_ARRAY) {
                        $unsealedType = $this->parseArrayShapeUnsealedType($tokens);
                    } else {
                        $unsealedType = $this->parseListShapeUnsealedType($tokens);
                    }
                    $tokens->skipNewLineTokensAndConsumeComments();
                }
                $tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA);
                break;
            }
            $items[] = $this->parseArrayShapeItem($tokens);
            $tokens->skipNewLineTokensAndConsumeComments();
            if (!$tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA)) {
                $done = \true;
            }
            if ($tokens->currentTokenType() !== Lexer::TOKEN_COMMENT) {
                continue;
            }
            $tokens->next();
        } while (!$done);
        $tokens->skipNewLineTokensAndConsumeComments();
        $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_CURLY_BRACKET);
        if ($sealed) {
            return Ast\Type\ArrayShapeNode::createSealed($items, $kind);
        }
        return Ast\Type\ArrayShapeNode::createUnsealed($items, $unsealedType, $kind);
    }
    /** @phpstan-impure */
    private function parseArrayShapeItem(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\ArrayShapeItemNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        // parse any comments above the item
        $tokens->skipNewLineTokensAndConsumeComments();
        try {
            $tokens->pushSavePoint();
            $key = $this->parseArrayShapeKey($tokens);
            $optional = $tokens->tryConsumeTokenType(Lexer::TOKEN_NULLABLE);
            $tokens->consumeTokenType(Lexer::TOKEN_COLON);
            $value = $this->parse($tokens);
            $tokens->dropSavePoint();
            return $this->enrichWithAttributes($tokens, new Ast\Type\ArrayShapeItemNode($key, $optional, $value), $startLine, $startIndex);
        } catch (\PHPStan\PhpDocParser\Parser\ParserException $e) {
            $tokens->rollback();
            $value = $this->parse($tokens);
            return $this->enrichWithAttributes($tokens, new Ast\Type\ArrayShapeItemNode(null, \false, $value), $startLine, $startIndex);
        }
    }
    /**
     * @phpstan-impure
     * @return Ast\ConstExpr\ConstExprIntegerNode|Ast\ConstExpr\ConstExprStringNode|Ast\Type\IdentifierTypeNode
     */
    private function parseArrayShapeKey(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens)
    {
        $startIndex = $tokens->currentTokenIndex();
        $startLine = $tokens->currentTokenLine();
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_INTEGER)) {
            $key = new Ast\ConstExpr\ConstExprIntegerNode(str_replace('_', '', $tokens->currentTokenValue()));
            $tokens->next();
        } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_SINGLE_QUOTED_STRING)) {
            $key = new Ast\ConstExpr\ConstExprStringNode(\PHPStan\PhpDocParser\Parser\StringUnescaper::unescapeString($tokens->currentTokenValue()), Ast\ConstExpr\ConstExprStringNode::SINGLE_QUOTED);
            $tokens->next();
        } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_DOUBLE_QUOTED_STRING)) {
            $key = new Ast\ConstExpr\ConstExprStringNode(\PHPStan\PhpDocParser\Parser\StringUnescaper::unescapeString($tokens->currentTokenValue()), Ast\ConstExpr\ConstExprStringNode::DOUBLE_QUOTED);
            $tokens->next();
        } else {
            $key = new Ast\Type\IdentifierTypeNode($tokens->currentTokenValue());
            $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
        }
        return $this->enrichWithAttributes($tokens, $key, $startLine, $startIndex);
    }
    /**
     * @phpstan-impure
     */
    private function parseArrayShapeUnsealedType(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\ArrayShapeUnsealedTypeNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        $tokens->consumeTokenType(Lexer::TOKEN_OPEN_ANGLE_BRACKET);
        $tokens->skipNewLineTokensAndConsumeComments();
        $valueType = $this->parse($tokens);
        $tokens->skipNewLineTokensAndConsumeComments();
        $keyType = null;
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA)) {
            $tokens->skipNewLineTokensAndConsumeComments();
            $keyType = $valueType;
            $valueType = $this->parse($tokens);
            $tokens->skipNewLineTokensAndConsumeComments();
        }
        $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_ANGLE_BRACKET);
        return $this->enrichWithAttributes($tokens, new Ast\Type\ArrayShapeUnsealedTypeNode($valueType, $keyType), $startLine, $startIndex);
    }
    /**
     * @phpstan-impure
     */
    private function parseListShapeUnsealedType(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\ArrayShapeUnsealedTypeNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        $tokens->consumeTokenType(Lexer::TOKEN_OPEN_ANGLE_BRACKET);
        $tokens->skipNewLineTokensAndConsumeComments();
        $valueType = $this->parse($tokens);
        $tokens->skipNewLineTokensAndConsumeComments();
        $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_ANGLE_BRACKET);
        return $this->enrichWithAttributes($tokens, new Ast\Type\ArrayShapeUnsealedTypeNode($valueType, null), $startLine, $startIndex);
    }
    /**
     * @phpstan-impure
     */
    private function parseObjectShape(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\ObjectShapeNode
    {
        $tokens->consumeTokenType(Lexer::TOKEN_OPEN_CURLY_BRACKET);
        $items = [];
        do {
            $tokens->skipNewLineTokensAndConsumeComments();
            if ($tokens->tryConsumeTokenType(Lexer::TOKEN_CLOSE_CURLY_BRACKET)) {
                return new Ast\Type\ObjectShapeNode($items);
            }
            $items[] = $this->parseObjectShapeItem($tokens);
            $tokens->skipNewLineTokensAndConsumeComments();
        } while ($tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA));
        $tokens->skipNewLineTokensAndConsumeComments();
        $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_CURLY_BRACKET);
        return new Ast\Type\ObjectShapeNode($items);
    }
    /** @phpstan-impure */
    private function parseObjectShapeItem(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\Type\ObjectShapeItemNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        $tokens->skipNewLineTokensAndConsumeComments();
        $key = $this->parseObjectShapeKey($tokens);
        $optional = $tokens->tryConsumeTokenType(Lexer::TOKEN_NULLABLE);
        $tokens->consumeTokenType(Lexer::TOKEN_COLON);
        $value = $this->parse($tokens);
        return $this->enrichWithAttributes($tokens, new Ast\Type\ObjectShapeItemNode($key, $optional, $value), $startLine, $startIndex);
    }
    /**
     * @phpstan-impure
     * @return Ast\ConstExpr\ConstExprStringNode|Ast\Type\IdentifierTypeNode
     */
    private function parseObjectShapeKey(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens)
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_SINGLE_QUOTED_STRING)) {
            $key = new Ast\ConstExpr\ConstExprStringNode(\PHPStan\PhpDocParser\Parser\StringUnescaper::unescapeString($tokens->currentTokenValue()), Ast\ConstExpr\ConstExprStringNode::SINGLE_QUOTED);
            $tokens->next();
        } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_DOUBLE_QUOTED_STRING)) {
            $key = new Ast\ConstExpr\ConstExprStringNode(\PHPStan\PhpDocParser\Parser\StringUnescaper::unescapeString($tokens->currentTokenValue()), Ast\ConstExpr\ConstExprStringNode::DOUBLE_QUOTED);
            $tokens->next();
        } else {
            $key = new Ast\Type\IdentifierTypeNode($tokens->currentTokenValue());
            $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
        }
        return $this->enrichWithAttributes($tokens, $key, $startLine, $startIndex);
    }
}
