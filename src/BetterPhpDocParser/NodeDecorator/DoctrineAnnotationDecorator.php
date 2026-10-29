<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\NodeDecorator;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNullNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine\DoctrineAnnotation;
use PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine\DoctrineArgument;
use PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine\DoctrineArray;
use PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine\DoctrineTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\BetterPhpDocParser\ValueObject\DoctrineAnnotation\SilentKeyMap;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\PHPStan\ObjectTypeSpecifier;
/**
 * Converts the Doctrine annotations that phpdoc-parser parsed to Rector Doctrine annotation nodes
 */
final class DoctrineAnnotationDecorator implements PhpDocNodeDecoratorInterface
{
    /**
     * @readonly
     */
    private ClassAnnotationMatcher $classAnnotationMatcher;
    /**
     * @readonly
     */
    private TokenIteratorFactory $tokenIteratorFactory;
    /**
     * @readonly
     */
    private AttributeMirrorer $attributeMirrorer;
    /**
     * @readonly
     */
    private ObjectTypeSpecifier $objectTypeSpecifier;
    /**
     * Special short annotations, that are resolved as FQN by Doctrine annotation parser
     * @var string[]
     */
    private const ALLOWED_SHORT_ANNOTATIONS = ['Target'];
    public function __construct(ClassAnnotationMatcher $classAnnotationMatcher, TokenIteratorFactory $tokenIteratorFactory, AttributeMirrorer $attributeMirrorer, ObjectTypeSpecifier $objectTypeSpecifier)
    {
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->tokenIteratorFactory = $tokenIteratorFactory;
        $this->attributeMirrorer = $attributeMirrorer;
        $this->objectTypeSpecifier = $objectTypeSpecifier;
    }
    public function decorate(PhpDocNode $phpDocNode, Node $phpNode): void
    {
        $incompleteAnnotationContent = null;
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            // the next children are the rest of an incomplete annotation, e.g. its nested annotations; keep them
            if ($incompleteAnnotationContent !== null) {
                $incompleteAnnotationContent .= "\n" . $phpDocChildNode;
                if ($this->isClosedContent($incompleteAnnotationContent)) {
                    $incompleteAnnotationContent = null;
                }
                continue;
            }
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            // the value ends before a nested annotation on the next line
            if ($phpDocChildNode->value instanceof InvalidTagValueNode) {
                if (!$this->isClosedContent($phpDocChildNode->value->value)) {
                    $incompleteAnnotationContent = $phpDocChildNode->value->value;
                }
                continue;
            }
            if ($phpDocChildNode->value instanceof DoctrineTagValueNode) {
                $fullyQualifiedAnnotationClass = $this->resolveDoctrineTagClass($phpDocChildNode->name, $phpNode);
                if ($fullyQualifiedAnnotationClass === null) {
                    continue;
                }
                $doctrineTagValueNode = $phpDocChildNode->value;
                $originalContent = '(' . implode(', ', $doctrineTagValueNode->annotation->arguments) . ')' . $doctrineTagValueNode->description;
                $values = $this->createArrayItemNodes($doctrineTagValueNode->annotation->arguments, $phpNode);
                $phpDocNode->children[$key] = $this->createSpacelessPhpDocTagNode($phpDocChildNode, $fullyQualifiedAnnotationClass, $originalContent, $values);
                continue;
            }
            if (!$phpDocChildNode->value instanceof GenericTagValueNode) {
                continue;
            }
            // known doc tag to annotation class
            $fullyQualifiedAnnotationClass = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($phpDocChildNode->name, $phpNode);
            // not an annotations class
            if (strpos($fullyQualifiedAnnotationClass, '\\') === \false && !in_array($fullyQualifiedAnnotationClass, self::ALLOWED_SHORT_ANNOTATIONS, \true)) {
                continue;
            }
            $phpDocNode->children[$key] = $this->createSpacelessPhpDocTagNode($phpDocChildNode, $fullyQualifiedAnnotationClass, $phpDocChildNode->value->value, []);
        }
    }
    /**
     * @param ArrayItemNode[] $values
     */
    private function createSpacelessPhpDocTagNode(PhpDocTagNode $phpDocTagNode, string $fullyQualifiedAnnotationClass, string $originalContent, array $values): SpacelessPhpDocTagNode
    {
        $identifierTypeNode = new IdentifierTypeNode($phpDocTagNode->name);
        $identifierTypeNode->setAttribute(PhpDocAttributeKey::RESOLVED_CLASS, $fullyQualifiedAnnotationClass);
        $doctrineAnnotationTagValueNode = new DoctrineAnnotationTagValueNode($identifierTypeNode, $originalContent, $values, SilentKeyMap::CLASS_NAMES_TO_SILENT_KEYS[$fullyQualifiedAnnotationClass] ?? null);
        $doctrineAnnotationTagValueNode->setAttribute(PhpDocAttributeKey::START_AND_END, $phpDocTagNode->getAttribute(PhpDocAttributeKey::START_AND_END));
        $spacelessPhpDocTagNode = new SpacelessPhpDocTagNode($phpDocTagNode->name, $doctrineAnnotationTagValueNode);
        $this->attributeMirrorer->mirror($phpDocTagNode, $spacelessPhpDocTagNode);
        return $spacelessPhpDocTagNode;
    }
    private function resolveDoctrineTagClass(string $tagName, Node $phpNode): ?string
    {
        $type = $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType($phpNode, new ObjectType(ltrim($tagName, '@')), $phpNode->getAttribute(AttributeKey::SCOPE));
        if ($type instanceof ShortenedObjectType || $type instanceof AliasedObjectType) {
            return $type->getFullyQualifiedName();
        }
        if ($type instanceof ObjectType) {
            return $type->getClassName();
        }
        return null;
    }
    /**
     * @param DoctrineArgument[] $doctrineArguments
     * @return ArrayItemNode[]
     */
    private function createArrayItemNodes(array $doctrineArguments, Node $phpNode): array
    {
        $arrayItemNodes = [];
        foreach ($doctrineArguments as $doctrineArgument) {
            $arrayItemNodes[] = new ArrayItemNode($this->createValue($doctrineArgument->value, $phpNode), ($nullsafeVariable1 = $doctrineArgument->key) ? $nullsafeVariable1->name : null);
        }
        return $arrayItemNodes;
    }
    /**
     * @param mixed $value
     * @return mixed
     */
    private function createValue($value, Node $phpNode)
    {
        if ($value instanceof DoctrineAnnotation) {
            $identifierTypeNode = new IdentifierTypeNode($value->name);
            $identifierTypeNode->setAttribute(PhpDocAttributeKey::RESOLVED_CLASS, $this->classAnnotationMatcher->resolveTagFullyQualifiedName($value->name, $phpNode));
            return new DoctrineAnnotationTagValueNode($identifierTypeNode, '(' . implode(', ', $value->arguments) . ')', $this->createArrayItemNodes($value->arguments, $phpNode));
        }
        if ($value instanceof DoctrineArray) {
            $arrayItemNodes = [];
            foreach ($value->items as $doctrineArrayItem) {
                $key = $doctrineArrayItem->key instanceof ConstExprIntegerNode ? (int) $doctrineArrayItem->key->value : $this->createValue($doctrineArrayItem->key, $phpNode);
                $arrayItemNodes[] = new ArrayItemNode($this->createValue($doctrineArrayItem->value, $phpNode), $key);
            }
            return new CurlyListNode($arrayItemNodes);
        }
        if ($value instanceof IdentifierTypeNode) {
            switch (strtolower($value->name)) {
                case 'true':
                    return new ConstExprTrueNode();
                case 'false':
                    return new ConstExprFalseNode();
                case 'null':
                    return null;
                default:
                    return $value->name;
            }
        }
        if ($value instanceof ConstExprStringNode) {
            return $value->quoteType === ConstExprStringNode::DOUBLE_QUOTED ? new StringNode($value->value) : $value->value;
        }
        if ($value instanceof ConstExprNullNode) {
            return null;
        }
        if ($value instanceof ConstExprIntegerNode || $value instanceof ConstExprTrueNode || $value instanceof ConstExprFalseNode) {
            return $value;
        }
        // class constant fetch, float
        if ($value instanceof ConstExprNode) {
            return (string) $value;
        }
        return $value;
    }
    /**
     * This is closed block, e.g. {( ... )},
     * false on: {( ... )
     */
    private function isClosedContent(string $composedContent): bool
    {
        $composedTokenIterator = $this->tokenIteratorFactory->create($composedContent);
        $tokenCount = $composedTokenIterator->getTokenCount();
        $openBracketCount = 0;
        $closeBracketCount = 0;
        if ($composedContent === '') {
            return \true;
        }
        do {
            if ($composedTokenIterator->isCurrentTokenType(Lexer::TOKEN_OPEN_CURLY_BRACKET, Lexer::TOKEN_OPEN_PARENTHESES) || strpos($composedTokenIterator->currentTokenValue(), '{') !== \false || strpos($composedTokenIterator->currentTokenValue(), '(') !== \false) {
                ++$openBracketCount;
            }
            if ($composedTokenIterator->isCurrentTokenType(Lexer::TOKEN_CLOSE_CURLY_BRACKET, Lexer::TOKEN_CLOSE_PARENTHESES) || strpos($composedTokenIterator->currentTokenValue(), '}') !== \false || strpos($composedTokenIterator->currentTokenValue(), ')') !== \false) {
                ++$closeBracketCount;
            }
            $composedTokenIterator->next();
        } while ($composedTokenIterator->currentTokenIndex() < $tokenCount - 1);
        return $openBracketCount === $closeBracketCount;
    }
}
