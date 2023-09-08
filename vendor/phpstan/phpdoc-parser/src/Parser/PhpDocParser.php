<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Parser;

use LogicException;
use PHPStan\PhpDocParser\Ast;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\ShouldNotHappenException;
use function array_key_exists;
use function array_values;
use function count;
use function rtrim;
use function str_replace;
use function trim;
/**
 * @phpstan-import-type ValueType from Doctrine\DoctrineArgument as DoctrineValueType
 */
class PhpDocParser
{
    private const DISALLOWED_DESCRIPTION_START_TOKENS = [Lexer::TOKEN_UNION, Lexer::TOKEN_INTERSECTION];
    /** @var TypeParser */
    private $typeParser;
    /** @var ConstExprParser */
    private $constantExprParser;
    /** @var ConstExprParser */
    private $doctrineConstantExprParser;
    /** @var bool */
    private $requireWhitespaceBeforeDescription;
    /** @var bool */
    private $preserveTypeAliasesWithInvalidTypes;
    /** @var bool */
    private $parseDoctrineAnnotations;
    /** @var bool */
    private $useLinesAttributes;
    /** @var bool */
    private $useIndexAttributes;
    /** @var bool */
    private $textBetweenTagsBelongsToDescription;
    /**
     * @param array{lines?: bool, indexes?: bool} $usedAttributes
     */
    public function __construct(\PHPStan\PhpDocParser\Parser\TypeParser $typeParser, \PHPStan\PhpDocParser\Parser\ConstExprParser $constantExprParser, bool $requireWhitespaceBeforeDescription = \false, bool $preserveTypeAliasesWithInvalidTypes = \false, array $usedAttributes = [], bool $parseDoctrineAnnotations = \false, bool $textBetweenTagsBelongsToDescription = \false)
    {
        $this->typeParser = $typeParser;
        $this->constantExprParser = $constantExprParser;
        $this->doctrineConstantExprParser = $constantExprParser->toDoctrine();
        $this->requireWhitespaceBeforeDescription = $requireWhitespaceBeforeDescription;
        $this->preserveTypeAliasesWithInvalidTypes = $preserveTypeAliasesWithInvalidTypes;
        $this->parseDoctrineAnnotations = $parseDoctrineAnnotations;
        $this->useLinesAttributes = $usedAttributes['lines'] ?? \false;
        $this->useIndexAttributes = $usedAttributes['indexes'] ?? \false;
        $this->textBetweenTagsBelongsToDescription = $textBetweenTagsBelongsToDescription;
    }
    public function parse(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\PhpDocNode
    {
        $tokens->consumeTokenType(Lexer::TOKEN_OPEN_PHPDOC);
        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $children = [];
        if ($this->parseDoctrineAnnotations) {
            if (!$tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
                $lastChild = $this->parseChild($tokens);
                $children[] = $lastChild;
                while (!$tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
                    if ($lastChild instanceof Ast\PhpDoc\PhpDocTagNode && ($lastChild->value instanceof Doctrine\DoctrineTagValueNode || $lastChild->value instanceof Ast\PhpDoc\GenericTagValueNode)) {
                        $tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
                        if ($tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
                            break;
                        }
                        $lastChild = $this->parseChild($tokens);
                        $children[] = $lastChild;
                        continue;
                    }
                    if (!$tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL)) {
                        break;
                    }
                    if ($tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
                        break;
                    }
                    $lastChild = $this->parseChild($tokens);
                    $children[] = $lastChild;
                }
            }
        } else {
            if (!$tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
                $children[] = $this->parseChild($tokens);
                while ($tokens->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL) && !$tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
                    $children[] = $this->parseChild($tokens);
                }
            }
        }
        try {
            $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_PHPDOC);
        } catch (\PHPStan\PhpDocParser\Parser\ParserException $e) {
            $name = '';
            $startLine = $tokens->currentTokenLine();
            $startIndex = $tokens->currentTokenIndex();
            if (count($children) > 0) {
                $lastChild = $children[count($children) - 1];
                if ($lastChild instanceof Ast\PhpDoc\PhpDocTagNode) {
                    $name = $lastChild->name;
                    $startLine = $tokens->currentTokenLine();
                    $startIndex = $tokens->currentTokenIndex();
                }
            }
            $tag = new Ast\PhpDoc\PhpDocTagNode($name, $this->enrichWithAttributes($tokens, new Ast\PhpDoc\InvalidTagValueNode($e->getMessage(), $e), $startLine, $startIndex));
            $tokens->forwardToTheEnd();
            return $this->enrichWithAttributes($tokens, new Ast\PhpDoc\PhpDocNode([$this->enrichWithAttributes($tokens, $tag, $startLine, $startIndex)]), 1, 0);
        }
        return $this->enrichWithAttributes($tokens, new Ast\PhpDoc\PhpDocNode(array_values($children)), 1, 0);
    }
    /** @phpstan-impure */
    private function parseChild(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\PhpDocChildNode
    {
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_PHPDOC_TAG)) {
            $startLine = $tokens->currentTokenLine();
            $startIndex = $tokens->currentTokenIndex();
            return $this->enrichWithAttributes($tokens, $this->parseTag($tokens), $startLine, $startIndex);
        }
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_DOCTRINE_TAG)) {
            $startLine = $tokens->currentTokenLine();
            $startIndex = $tokens->currentTokenIndex();
            $tag = $tokens->currentTokenValue();
            $tokens->next();
            $tagStartLine = $tokens->currentTokenLine();
            $tagStartIndex = $tokens->currentTokenIndex();
            return $this->enrichWithAttributes($tokens, new Ast\PhpDoc\PhpDocTagNode($tag, $this->enrichWithAttributes($tokens, $this->parseDoctrineTagValue($tokens, $tag), $tagStartLine, $tagStartIndex)), $startLine, $startIndex);
        }
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        $text = $this->parseText($tokens);
        return $this->enrichWithAttributes($tokens, $text, $startLine, $startIndex);
    }
    /**
     * @template T of Ast\Node
     * @param T $tag
     * @return T
     */
    private function enrichWithAttributes(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, Ast\Node $tag, int $startLine, int $startIndex) : Ast\Node
    {
        if ($this->useLinesAttributes) {
            $tag->setAttribute(Ast\Attribute::START_LINE, $startLine);
            $tag->setAttribute(Ast\Attribute::END_LINE, $tokens->currentTokenLine());
        }
        if ($this->useIndexAttributes) {
            $tag->setAttribute(Ast\Attribute::START_INDEX, $startIndex);
            $tag->setAttribute(Ast\Attribute::END_INDEX, $tokens->endIndexOfLastRelevantToken());
        }
        return $tag;
    }
    private function parseText(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\PhpDocTextNode
    {
        $text = '';
        $endTokens = [Lexer::TOKEN_PHPDOC_EOL, Lexer::TOKEN_CLOSE_PHPDOC, Lexer::TOKEN_END];
        if ($this->textBetweenTagsBelongsToDescription) {
            $endTokens = [Lexer::TOKEN_CLOSE_PHPDOC, Lexer::TOKEN_END];
        }
        $savepoint = \false;
        // if the next token is EOL, everything below is skipped and empty string is returned
        while ($this->textBetweenTagsBelongsToDescription || !$tokens->isCurrentTokenType(Lexer::TOKEN_PHPDOC_EOL)) {
            $tmpText = $tokens->getSkippedHorizontalWhiteSpaceIfAny() . $tokens->joinUntil(Lexer::TOKEN_PHPDOC_EOL, ...$endTokens);
            $text .= $tmpText;
            // stop if we're not at EOL - meaning it's the end of PHPDoc
            if (!$tokens->isCurrentTokenType(Lexer::TOKEN_PHPDOC_EOL)) {
                break;
            }
            if ($this->textBetweenTagsBelongsToDescription) {
                if (!$savepoint) {
                    $tokens->pushSavePoint();
                    $savepoint = \true;
                } elseif ($tmpText !== '') {
                    $tokens->dropSavePoint();
                    $tokens->pushSavePoint();
                }
            }
            $tokens->pushSavePoint();
            $tokens->next();
            // if we're at EOL, check what's next
            // if next is a PHPDoc tag, EOL, or end of PHPDoc, stop
            if ($tokens->isCurrentTokenType(Lexer::TOKEN_PHPDOC_TAG, Lexer::TOKEN_DOCTRINE_TAG, ...$endTokens)) {
                $tokens->rollback();
                break;
            }
            // otherwise if the next is text, continue building the description string
            $tokens->dropSavePoint();
            $text .= $tokens->getDetectedNewline() ?? "\n";
        }
        if ($savepoint) {
            $tokens->rollback();
            $text = rtrim($text, $tokens->getDetectedNewline() ?? "\n");
        }
        return new Ast\PhpDoc\PhpDocTextNode(trim($text, " \t"));
    }
    private function parseOptionalDescriptionAfterDoctrineTag(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : string
    {
        $text = '';
        $endTokens = [Lexer::TOKEN_PHPDOC_EOL, Lexer::TOKEN_CLOSE_PHPDOC, Lexer::TOKEN_END];
        if ($this->textBetweenTagsBelongsToDescription) {
            $endTokens = [Lexer::TOKEN_CLOSE_PHPDOC, Lexer::TOKEN_END];
        }
        $savepoint = \false;
        // if the next token is EOL, everything below is skipped and empty string is returned
        while ($this->textBetweenTagsBelongsToDescription || !$tokens->isCurrentTokenType(Lexer::TOKEN_PHPDOC_EOL)) {
            $tmpText = $tokens->getSkippedHorizontalWhiteSpaceIfAny() . $tokens->joinUntil(Lexer::TOKEN_PHPDOC_TAG, Lexer::TOKEN_DOCTRINE_TAG, Lexer::TOKEN_PHPDOC_EOL, ...$endTokens);
            $text .= $tmpText;
            // stop if we're not at EOL - meaning it's the end of PHPDoc
            if (!$tokens->isCurrentTokenType(Lexer::TOKEN_PHPDOC_EOL)) {
                if (!$tokens->isPrecededByHorizontalWhitespace()) {
                    return trim($text . $this->parseText($tokens)->text, " \t");
                }
                if ($tokens->isCurrentTokenType(Lexer::TOKEN_PHPDOC_TAG)) {
                    $tokens->pushSavePoint();
                    $child = $this->parseChild($tokens);
                    if ($child instanceof Ast\PhpDoc\PhpDocTagNode) {
                        if ($child->value instanceof Ast\PhpDoc\GenericTagValueNode || $child->value instanceof Doctrine\DoctrineTagValueNode) {
                            $tokens->rollback();
                            break;
                        }
                        if ($child->value instanceof Ast\PhpDoc\InvalidTagValueNode) {
                            $tokens->rollback();
                            $tokens->pushSavePoint();
                            $tokens->next();
                            if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
                                $tokens->rollback();
                                break;
                            }
                            $tokens->rollback();
                            return trim($text . $this->parseText($tokens)->text, " \t");
                        }
                    }
                    $tokens->rollback();
                    return trim($text . $this->parseText($tokens)->text, " \t");
                }
                break;
            }
            if ($this->textBetweenTagsBelongsToDescription) {
                if (!$savepoint) {
                    $tokens->pushSavePoint();
                    $savepoint = \true;
                } elseif ($tmpText !== '') {
                    $tokens->dropSavePoint();
                    $tokens->pushSavePoint();
                }
            }
            $tokens->pushSavePoint();
            $tokens->next();
            // if we're at EOL, check what's next
            // if next is a PHPDoc tag, EOL, or end of PHPDoc, stop
            if ($tokens->isCurrentTokenType(Lexer::TOKEN_PHPDOC_TAG, Lexer::TOKEN_DOCTRINE_TAG, ...$endTokens)) {
                $tokens->rollback();
                break;
            }
            // otherwise if the next is text, continue building the description string
            $tokens->dropSavePoint();
            $text .= $tokens->getDetectedNewline() ?? "\n";
        }
        if ($savepoint) {
            $tokens->rollback();
            $text = rtrim($text, $tokens->getDetectedNewline() ?? "\n");
        }
        return trim($text, " \t");
    }
    public function parseTag(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\PhpDocTagNode
    {
        $tag = $tokens->currentTokenValue();
        $tokens->next();
        $value = $this->parseTagValue($tokens, $tag);
        return new Ast\PhpDoc\PhpDocTagNode($tag, $value);
    }
    public function parseTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, string $tag) : Ast\PhpDoc\PhpDocTagValueNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        try {
            $tokens->pushSavePoint();
            switch ($tag) {
                case '@param':
                case '@phpstan-param':
                case '@psalm-param':
                    $tagValue = $this->parseParamTagValue($tokens);
                    break;
                case '@var':
                case '@phpstan-var':
                case '@psalm-var':
                    $tagValue = $this->parseVarTagValue($tokens);
                    break;
                case '@return':
                case '@phpstan-return':
                case '@psalm-return':
                    $tagValue = $this->parseReturnTagValue($tokens);
                    break;
                case '@throws':
                case '@phpstan-throws':
                    $tagValue = $this->parseThrowsTagValue($tokens);
                    break;
                case '@mixin':
                    $tagValue = $this->parseMixinTagValue($tokens);
                    break;
                case '@deprecated':
                    $tagValue = $this->parseDeprecatedTagValue($tokens);
                    break;
                case '@property':
                case '@property-read':
                case '@property-write':
                case '@phpstan-property':
                case '@phpstan-property-read':
                case '@phpstan-property-write':
                case '@psalm-property':
                case '@psalm-property-read':
                case '@psalm-property-write':
                    $tagValue = $this->parsePropertyTagValue($tokens);
                    break;
                case '@method':
                case '@phpstan-method':
                case '@psalm-method':
                    $tagValue = $this->parseMethodTagValue($tokens);
                    break;
                case '@template':
                case '@phpstan-template':
                case '@psalm-template':
                case '@template-covariant':
                case '@phpstan-template-covariant':
                case '@psalm-template-covariant':
                case '@template-contravariant':
                case '@phpstan-template-contravariant':
                case '@psalm-template-contravariant':
                    $tagValue = $this->parseTemplateTagValue($tokens, \true);
                    break;
                case '@extends':
                case '@phpstan-extends':
                case '@template-extends':
                    $tagValue = $this->parseExtendsTagValue('@extends', $tokens);
                    break;
                case '@implements':
                case '@phpstan-implements':
                case '@template-implements':
                    $tagValue = $this->parseExtendsTagValue('@implements', $tokens);
                    break;
                case '@use':
                case '@phpstan-use':
                case '@template-use':
                    $tagValue = $this->parseExtendsTagValue('@use', $tokens);
                    break;
                case '@phpstan-type':
                case '@psalm-type':
                    $tagValue = $this->parseTypeAliasTagValue($tokens);
                    break;
                case '@phpstan-import-type':
                case '@psalm-import-type':
                    $tagValue = $this->parseTypeAliasImportTagValue($tokens);
                    break;
                case '@phpstan-assert':
                case '@phpstan-assert-if-true':
                case '@phpstan-assert-if-false':
                case '@psalm-assert':
                case '@psalm-assert-if-true':
                case '@psalm-assert-if-false':
                    $tagValue = $this->parseAssertTagValue($tokens);
                    break;
                case '@phpstan-this-out':
                case '@phpstan-self-out':
                case '@psalm-this-out':
                case '@psalm-self-out':
                    $tagValue = $this->parseSelfOutTagValue($tokens);
                    break;
                case '@param-out':
                case '@phpstan-param-out':
                case '@psalm-param-out':
                    $tagValue = $this->parseParamOutTagValue($tokens);
                    break;
                default:
                    if ($this->parseDoctrineAnnotations) {
                        if ($tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
                            $tagValue = $this->parseDoctrineTagValue($tokens, $tag);
                        } else {
                            $tagValue = new Ast\PhpDoc\GenericTagValueNode($this->parseOptionalDescriptionAfterDoctrineTag($tokens));
                        }
                        break;
                    }
                    $tagValue = new Ast\PhpDoc\GenericTagValueNode($this->parseOptionalDescription($tokens));
                    break;
            }
            $tokens->dropSavePoint();
        } catch (\PHPStan\PhpDocParser\Parser\ParserException $e) {
            $tokens->rollback();
            $tagValue = new Ast\PhpDoc\InvalidTagValueNode($this->parseOptionalDescription($tokens), $e);
        }
        return $this->enrichWithAttributes($tokens, $tagValue, $startLine, $startIndex);
    }
    private function parseDoctrineTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, string $tag) : Ast\PhpDoc\PhpDocTagValueNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        return new Doctrine\DoctrineTagValueNode($this->enrichWithAttributes($tokens, new Doctrine\DoctrineAnnotation($tag, $this->parseDoctrineArguments($tokens, \false)), $startLine, $startIndex), $this->parseOptionalDescriptionAfterDoctrineTag($tokens));
    }
    /**
     * @return list<Doctrine\DoctrineArgument>
     */
    private function parseDoctrineArguments(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, bool $deep) : array
    {
        if (!$tokens->isCurrentTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
            return [];
        }
        if (!$deep) {
            $tokens->addEndOfLineToSkippedTokens();
        }
        $arguments = [];
        try {
            $tokens->consumeTokenType(Lexer::TOKEN_OPEN_PARENTHESES);
            do {
                if ($tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_PARENTHESES)) {
                    break;
                }
                $arguments[] = $this->parseDoctrineArgument($tokens);
            } while ($tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA));
        } finally {
            if (!$deep) {
                $tokens->removeEndOfLineFromSkippedTokens();
            }
        }
        $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_PARENTHESES);
        return $arguments;
    }
    private function parseDoctrineArgument(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Doctrine\DoctrineArgument
    {
        if (!$tokens->isCurrentTokenType(Lexer::TOKEN_IDENTIFIER)) {
            $startLine = $tokens->currentTokenLine();
            $startIndex = $tokens->currentTokenIndex();
            return $this->enrichWithAttributes($tokens, new Doctrine\DoctrineArgument(null, $this->parseDoctrineArgumentValue($tokens)), $startLine, $startIndex);
        }
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        try {
            $tokens->pushSavePoint();
            $currentValue = $tokens->currentTokenValue();
            $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
            $key = $this->enrichWithAttributes($tokens, new IdentifierTypeNode($currentValue), $startLine, $startIndex);
            $tokens->consumeTokenType(Lexer::TOKEN_EQUAL);
            $value = $this->parseDoctrineArgumentValue($tokens);
            $tokens->dropSavePoint();
            return $this->enrichWithAttributes($tokens, new Doctrine\DoctrineArgument($key, $value), $startLine, $startIndex);
        } catch (\PHPStan\PhpDocParser\Parser\ParserException $e) {
            $tokens->rollback();
            return $this->enrichWithAttributes($tokens, new Doctrine\DoctrineArgument(null, $this->parseDoctrineArgumentValue($tokens)), $startLine, $startIndex);
        }
    }
    /**
     * @return DoctrineValueType
     */
    private function parseDoctrineArgumentValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens)
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_PHPDOC_TAG, Lexer::TOKEN_DOCTRINE_TAG)) {
            $name = $tokens->currentTokenValue();
            $tokens->next();
            return $this->enrichWithAttributes($tokens, new Doctrine\DoctrineAnnotation($name, $this->parseDoctrineArguments($tokens, \true)), $startLine, $startIndex);
        }
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_OPEN_CURLY_BRACKET)) {
            $items = [];
            do {
                if ($tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_CURLY_BRACKET)) {
                    break;
                }
                $items[] = $this->parseDoctrineArrayItem($tokens);
            } while ($tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA));
            $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_CURLY_BRACKET);
            return $this->enrichWithAttributes($tokens, new Doctrine\DoctrineArray($items), $startLine, $startIndex);
        }
        $currentTokenValue = $tokens->currentTokenValue();
        $tokens->pushSavePoint();
        // because of ConstFetchNode
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_IDENTIFIER)) {
            $identifier = $this->enrichWithAttributes($tokens, new Ast\Type\IdentifierTypeNode($currentTokenValue), $startLine, $startIndex);
            if (!$tokens->isCurrentTokenType(Lexer::TOKEN_DOUBLE_COLON)) {
                $tokens->dropSavePoint();
                return $identifier;
            }
            $tokens->rollback();
            // because of ConstFetchNode
        } else {
            $tokens->dropSavePoint();
            // because of ConstFetchNode
        }
        $currentTokenValue = $tokens->currentTokenValue();
        $currentTokenType = $tokens->currentTokenType();
        $currentTokenOffset = $tokens->currentTokenOffset();
        $currentTokenLine = $tokens->currentTokenLine();
        try {
            $constExpr = $this->doctrineConstantExprParser->parse($tokens, \true);
            if ($constExpr instanceof Ast\ConstExpr\ConstExprArrayNode) {
                throw new \PHPStan\PhpDocParser\Parser\ParserException($currentTokenValue, $currentTokenType, $currentTokenOffset, Lexer::TOKEN_IDENTIFIER, null, $currentTokenLine);
            }
            return $constExpr;
        } catch (LogicException $e) {
            throw new \PHPStan\PhpDocParser\Parser\ParserException($currentTokenValue, $currentTokenType, $currentTokenOffset, Lexer::TOKEN_IDENTIFIER, null, $currentTokenLine);
        }
    }
    private function parseDoctrineArrayItem(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Doctrine\DoctrineArrayItem
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        try {
            $tokens->pushSavePoint();
            $key = $this->parseDoctrineArrayKey($tokens);
            if (!$tokens->tryConsumeTokenType(Lexer::TOKEN_EQUAL)) {
                if (!$tokens->tryConsumeTokenType(Lexer::TOKEN_COLON)) {
                    $tokens->consumeTokenType(Lexer::TOKEN_EQUAL);
                    // will throw exception
                }
            }
            $value = $this->parseDoctrineArgumentValue($tokens);
            $tokens->dropSavePoint();
            return $this->enrichWithAttributes($tokens, new Doctrine\DoctrineArrayItem($key, $value), $startLine, $startIndex);
        } catch (\PHPStan\PhpDocParser\Parser\ParserException $e) {
            $tokens->rollback();
            return $this->enrichWithAttributes($tokens, new Doctrine\DoctrineArrayItem(null, $this->parseDoctrineArgumentValue($tokens)), $startLine, $startIndex);
        }
    }
    /**
     * @return ConstExprIntegerNode|ConstExprStringNode|IdentifierTypeNode|ConstFetchNode
     */
    private function parseDoctrineArrayKey(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens)
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_INTEGER)) {
            $key = new Ast\ConstExpr\ConstExprIntegerNode(str_replace('_', '', $tokens->currentTokenValue()));
            $tokens->next();
        } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_DOCTRINE_ANNOTATION_STRING)) {
            $key = new Ast\ConstExpr\DoctrineConstExprStringNode(Ast\ConstExpr\DoctrineConstExprStringNode::unescape($tokens->currentTokenValue()));
            $tokens->next();
        } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_DOUBLE_QUOTED_STRING)) {
            $value = $tokens->currentTokenValue();
            $tokens->next();
            $key = $this->doctrineConstantExprParser->parseDoctrineString($value, $tokens);
        } else {
            $currentTokenValue = $tokens->currentTokenValue();
            $tokens->pushSavePoint();
            // because of ConstFetchNode
            if (!$tokens->tryConsumeTokenType(Lexer::TOKEN_IDENTIFIER)) {
                $tokens->dropSavePoint();
                throw new \PHPStan\PhpDocParser\Parser\ParserException($tokens->currentTokenValue(), $tokens->currentTokenType(), $tokens->currentTokenOffset(), Lexer::TOKEN_IDENTIFIER, null, $tokens->currentTokenLine());
            }
            if (!$tokens->isCurrentTokenType(Lexer::TOKEN_DOUBLE_COLON)) {
                $tokens->dropSavePoint();
                return $this->enrichWithAttributes($tokens, new IdentifierTypeNode($currentTokenValue), $startLine, $startIndex);
            }
            $tokens->rollback();
            $constExpr = $this->doctrineConstantExprParser->parse($tokens, \true);
            if (!$constExpr instanceof Ast\ConstExpr\ConstFetchNode) {
                throw new \PHPStan\PhpDocParser\Parser\ParserException($tokens->currentTokenValue(), $tokens->currentTokenType(), $tokens->currentTokenOffset(), Lexer::TOKEN_IDENTIFIER, null, $tokens->currentTokenLine());
            }
            return $constExpr;
        }
        return $this->enrichWithAttributes($tokens, $key, $startLine, $startIndex);
    }
    /**
     * @return Ast\PhpDoc\ParamTagValueNode|Ast\PhpDoc\TypelessParamTagValueNode
     */
    private function parseParamTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\PhpDocTagValueNode
    {
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_REFERENCE, Lexer::TOKEN_VARIADIC, Lexer::TOKEN_VARIABLE)) {
            $type = null;
        } else {
            $type = $this->typeParser->parse($tokens);
        }
        $isReference = $tokens->tryConsumeTokenType(Lexer::TOKEN_REFERENCE);
        $isVariadic = $tokens->tryConsumeTokenType(Lexer::TOKEN_VARIADIC);
        $parameterName = $this->parseRequiredVariableName($tokens);
        $description = $this->parseOptionalDescription($tokens);
        if ($type !== null) {
            return new Ast\PhpDoc\ParamTagValueNode($type, $isVariadic, $parameterName, $description, $isReference);
        }
        return new Ast\PhpDoc\TypelessParamTagValueNode($isVariadic, $parameterName, $description, $isReference);
    }
    private function parseVarTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\VarTagValueNode
    {
        $type = $this->typeParser->parse($tokens);
        $variableName = $this->parseOptionalVariableName($tokens);
        $description = $this->parseOptionalDescription($tokens, $variableName === '');
        return new Ast\PhpDoc\VarTagValueNode($type, $variableName, $description);
    }
    private function parseReturnTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\ReturnTagValueNode
    {
        $type = $this->typeParser->parse($tokens);
        $description = $this->parseOptionalDescription($tokens, \true);
        return new Ast\PhpDoc\ReturnTagValueNode($type, $description);
    }
    private function parseThrowsTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\ThrowsTagValueNode
    {
        $type = $this->typeParser->parse($tokens);
        $description = $this->parseOptionalDescription($tokens, \true);
        return new Ast\PhpDoc\ThrowsTagValueNode($type, $description);
    }
    private function parseMixinTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\MixinTagValueNode
    {
        $type = $this->typeParser->parse($tokens);
        $description = $this->parseOptionalDescription($tokens, \true);
        return new Ast\PhpDoc\MixinTagValueNode($type, $description);
    }
    private function parseDeprecatedTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\DeprecatedTagValueNode
    {
        $description = $this->parseOptionalDescription($tokens);
        return new Ast\PhpDoc\DeprecatedTagValueNode($description);
    }
    private function parsePropertyTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\PropertyTagValueNode
    {
        $type = $this->typeParser->parse($tokens);
        $parameterName = $this->parseRequiredVariableName($tokens);
        $description = $this->parseOptionalDescription($tokens);
        return new Ast\PhpDoc\PropertyTagValueNode($type, $parameterName, $description);
    }
    private function parseMethodTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\MethodTagValueNode
    {
        $isStatic = $tokens->tryConsumeTokenValue('static');
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        $returnTypeOrMethodName = $this->typeParser->parse($tokens);
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_IDENTIFIER)) {
            $returnType = $returnTypeOrMethodName;
            $methodName = $tokens->currentTokenValue();
            $tokens->next();
        } elseif ($returnTypeOrMethodName instanceof Ast\Type\IdentifierTypeNode) {
            $returnType = $isStatic ? $this->typeParser->enrichWithAttributes($tokens, new Ast\Type\IdentifierTypeNode('static'), $startLine, $startIndex) : null;
            $methodName = $returnTypeOrMethodName->name;
            $isStatic = \false;
        } else {
            $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
            // will throw exception
            exit;
        }
        $templateTypes = [];
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_OPEN_ANGLE_BRACKET)) {
            do {
                $startLine = $tokens->currentTokenLine();
                $startIndex = $tokens->currentTokenIndex();
                $templateTypes[] = $this->enrichWithAttributes($tokens, $this->parseTemplateTagValue($tokens, \false), $startLine, $startIndex);
            } while ($tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA));
            $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_ANGLE_BRACKET);
        }
        $parameters = [];
        $tokens->consumeTokenType(Lexer::TOKEN_OPEN_PARENTHESES);
        if (!$tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_PARENTHESES)) {
            $parameters[] = $this->parseMethodTagValueParameter($tokens);
            while ($tokens->tryConsumeTokenType(Lexer::TOKEN_COMMA)) {
                $parameters[] = $this->parseMethodTagValueParameter($tokens);
            }
        }
        $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_PARENTHESES);
        $description = $this->parseOptionalDescription($tokens);
        return new Ast\PhpDoc\MethodTagValueNode($isStatic, $returnType, $methodName, $parameters, $description, $templateTypes);
    }
    private function parseMethodTagValueParameter(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\MethodTagValueParameterNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        switch ($tokens->currentTokenType()) {
            case Lexer::TOKEN_IDENTIFIER:
            case Lexer::TOKEN_OPEN_PARENTHESES:
            case Lexer::TOKEN_NULLABLE:
                $parameterType = $this->typeParser->parse($tokens);
                break;
            default:
                $parameterType = null;
        }
        $isReference = $tokens->tryConsumeTokenType(Lexer::TOKEN_REFERENCE);
        $isVariadic = $tokens->tryConsumeTokenType(Lexer::TOKEN_VARIADIC);
        $parameterName = $tokens->currentTokenValue();
        $tokens->consumeTokenType(Lexer::TOKEN_VARIABLE);
        if ($tokens->tryConsumeTokenType(Lexer::TOKEN_EQUAL)) {
            $defaultValue = $this->constantExprParser->parse($tokens);
        } else {
            $defaultValue = null;
        }
        return $this->enrichWithAttributes($tokens, new Ast\PhpDoc\MethodTagValueParameterNode($parameterType, $isReference, $isVariadic, $parameterName, $defaultValue), $startLine, $startIndex);
    }
    private function parseTemplateTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, bool $parseDescription) : Ast\PhpDoc\TemplateTagValueNode
    {
        $name = $tokens->currentTokenValue();
        $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
        if ($tokens->tryConsumeTokenValue('of') || $tokens->tryConsumeTokenValue('as')) {
            $bound = $this->typeParser->parse($tokens);
        } else {
            $bound = null;
        }
        if ($tokens->tryConsumeTokenValue('=')) {
            $default = $this->typeParser->parse($tokens);
        } else {
            $default = null;
        }
        if ($parseDescription) {
            $description = $this->parseOptionalDescription($tokens);
        } else {
            $description = '';
        }
        return new Ast\PhpDoc\TemplateTagValueNode($name, $bound, $description, $default);
    }
    private function parseExtendsTagValue(string $tagName, \PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\PhpDocTagValueNode
    {
        $startLine = $tokens->currentTokenLine();
        $startIndex = $tokens->currentTokenIndex();
        $baseType = new IdentifierTypeNode($tokens->currentTokenValue());
        $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
        $type = $this->typeParser->parseGeneric($tokens, $this->typeParser->enrichWithAttributes($tokens, $baseType, $startLine, $startIndex));
        $description = $this->parseOptionalDescription($tokens);
        switch ($tagName) {
            case '@extends':
                return new Ast\PhpDoc\ExtendsTagValueNode($type, $description);
            case '@implements':
                return new Ast\PhpDoc\ImplementsTagValueNode($type, $description);
            case '@use':
                return new Ast\PhpDoc\UsesTagValueNode($type, $description);
        }
        throw new ShouldNotHappenException();
    }
    private function parseTypeAliasTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\TypeAliasTagValueNode
    {
        $alias = $tokens->currentTokenValue();
        $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
        // support psalm-type syntax
        $tokens->tryConsumeTokenType(Lexer::TOKEN_EQUAL);
        if ($this->preserveTypeAliasesWithInvalidTypes) {
            $startLine = $tokens->currentTokenLine();
            $startIndex = $tokens->currentTokenIndex();
            try {
                $type = $this->typeParser->parse($tokens);
                if (!$tokens->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
                    if (!$tokens->isCurrentTokenType(Lexer::TOKEN_PHPDOC_EOL)) {
                        throw new \PHPStan\PhpDocParser\Parser\ParserException($tokens->currentTokenValue(), $tokens->currentTokenType(), $tokens->currentTokenOffset(), Lexer::TOKEN_PHPDOC_EOL, null, $tokens->currentTokenLine());
                    }
                }
                return new Ast\PhpDoc\TypeAliasTagValueNode($alias, $type);
            } catch (\PHPStan\PhpDocParser\Parser\ParserException $e) {
                $this->parseOptionalDescription($tokens);
                return new Ast\PhpDoc\TypeAliasTagValueNode($alias, $this->enrichWithAttributes($tokens, new Ast\Type\InvalidTypeNode($e), $startLine, $startIndex));
            }
        }
        $type = $this->typeParser->parse($tokens);
        return new Ast\PhpDoc\TypeAliasTagValueNode($alias, $type);
    }
    private function parseTypeAliasImportTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\TypeAliasImportTagValueNode
    {
        $importedAlias = $tokens->currentTokenValue();
        $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
        $tokens->consumeTokenValue(Lexer::TOKEN_IDENTIFIER, 'from');
        $identifierStartLine = $tokens->currentTokenLine();
        $identifierStartIndex = $tokens->currentTokenIndex();
        $importedFrom = $tokens->currentTokenValue();
        $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
        $importedFromType = $this->enrichWithAttributes($tokens, new IdentifierTypeNode($importedFrom), $identifierStartLine, $identifierStartIndex);
        $importedAs = null;
        if ($tokens->tryConsumeTokenValue('as')) {
            $importedAs = $tokens->currentTokenValue();
            $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
        }
        return new Ast\PhpDoc\TypeAliasImportTagValueNode($importedAlias, $importedFromType, $importedAs);
    }
    /**
     * @return Ast\PhpDoc\AssertTagValueNode|Ast\PhpDoc\AssertTagPropertyValueNode|Ast\PhpDoc\AssertTagMethodValueNode
     */
    private function parseAssertTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\PhpDocTagValueNode
    {
        $isNegated = $tokens->tryConsumeTokenType(Lexer::TOKEN_NEGATED);
        $isEquality = $tokens->tryConsumeTokenType(Lexer::TOKEN_EQUAL);
        $type = $this->typeParser->parse($tokens);
        $parameter = $this->parseAssertParameter($tokens);
        $description = $this->parseOptionalDescription($tokens);
        if (array_key_exists('method', $parameter)) {
            return new Ast\PhpDoc\AssertTagMethodValueNode($type, $parameter['parameter'], $parameter['method'], $isNegated, $description, $isEquality);
        } elseif (array_key_exists('property', $parameter)) {
            return new Ast\PhpDoc\AssertTagPropertyValueNode($type, $parameter['parameter'], $parameter['property'], $isNegated, $description, $isEquality);
        }
        return new Ast\PhpDoc\AssertTagValueNode($type, $parameter['parameter'], $isNegated, $description, $isEquality);
    }
    /**
     * @return array{parameter: string}|array{parameter: string, property: string}|array{parameter: string, method: string}
     */
    private function parseAssertParameter(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : array
    {
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_THIS_VARIABLE)) {
            $parameter = '$this';
            $tokens->next();
        } else {
            $parameter = $tokens->currentTokenValue();
            $tokens->consumeTokenType(Lexer::TOKEN_VARIABLE);
        }
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_ARROW)) {
            $tokens->consumeTokenType(Lexer::TOKEN_ARROW);
            $propertyOrMethod = $tokens->currentTokenValue();
            $tokens->consumeTokenType(Lexer::TOKEN_IDENTIFIER);
            if ($tokens->tryConsumeTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
                $tokens->consumeTokenType(Lexer::TOKEN_CLOSE_PARENTHESES);
                return ['parameter' => $parameter, 'method' => $propertyOrMethod];
            }
            return ['parameter' => $parameter, 'property' => $propertyOrMethod];
        }
        return ['parameter' => $parameter];
    }
    private function parseSelfOutTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\SelfOutTagValueNode
    {
        $type = $this->typeParser->parse($tokens);
        $description = $this->parseOptionalDescription($tokens);
        return new Ast\PhpDoc\SelfOutTagValueNode($type, $description);
    }
    private function parseParamOutTagValue(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : Ast\PhpDoc\ParamOutTagValueNode
    {
        $type = $this->typeParser->parse($tokens);
        $parameterName = $this->parseRequiredVariableName($tokens);
        $description = $this->parseOptionalDescription($tokens);
        return new Ast\PhpDoc\ParamOutTagValueNode($type, $parameterName, $description);
    }
    private function parseOptionalVariableName(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : string
    {
        if ($tokens->isCurrentTokenType(Lexer::TOKEN_VARIABLE)) {
            $parameterName = $tokens->currentTokenValue();
            $tokens->next();
        } elseif ($tokens->isCurrentTokenType(Lexer::TOKEN_THIS_VARIABLE)) {
            $parameterName = '$this';
            $tokens->next();
        } else {
            $parameterName = '';
        }
        return $parameterName;
    }
    private function parseRequiredVariableName(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens) : string
    {
        $parameterName = $tokens->currentTokenValue();
        $tokens->consumeTokenType(Lexer::TOKEN_VARIABLE);
        return $parameterName;
    }
    private function parseOptionalDescription(\PHPStan\PhpDocParser\Parser\TokenIterator $tokens, bool $limitStartToken = \false) : string
    {
        if ($limitStartToken) {
            foreach (self::DISALLOWED_DESCRIPTION_START_TOKENS as $disallowedStartToken) {
                if (!$tokens->isCurrentTokenType($disallowedStartToken)) {
                    continue;
                }
                $tokens->consumeTokenType(Lexer::TOKEN_OTHER);
                // will throw exception
            }
            if ($this->requireWhitespaceBeforeDescription && !$tokens->isCurrentTokenType(Lexer::TOKEN_PHPDOC_EOL, Lexer::TOKEN_CLOSE_PHPDOC, Lexer::TOKEN_END) && !$tokens->isPrecededByHorizontalWhitespace()) {
                $tokens->consumeTokenType(Lexer::TOKEN_HORIZONTAL_WS);
                // will throw exception
            }
        }
        return $this->parseText($tokens)->text;
    }
}
