<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\NodeFactory;

use RectorPrefix202409\Nette\Utils\Strings;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Use_;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use Rector\BetterPhpDocParser\PhpDocParser\DoctrineAnnotationDecorator;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\ValueObject\AnnotationPropertyToAttributeClass;
use Rector\Php80\ValueObject\NestedAnnotationToAttribute;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\AttributeArrayNameInliner;
use RectorPrefix202409\Webmozart\Assert\Assert;
final class PhpNestedAttributeGroupFactory
{
    /**
     * @readonly
     * @var \Rector\PhpAttribute\AnnotationToAttributeMapper
     */
    private $annotationToAttributeMapper;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\AttributeNameFactory
     */
    private $attributeNameFactory;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\NamedArgsFactory
     */
    private $namedArgsFactory;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\AttributeArrayNameInliner
     */
    private $attributeArrayNameInliner;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory
     */
    private $tokenIteratorFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser
     */
    private $staticDoctrineAnnotationParser;
    public function __construct(AnnotationToAttributeMapper $annotationToAttributeMapper, \Rector\PhpAttribute\NodeFactory\AttributeNameFactory $attributeNameFactory, \Rector\PhpAttribute\NodeFactory\NamedArgsFactory $namedArgsFactory, AttributeArrayNameInliner $attributeArrayNameInliner, TokenIteratorFactory $tokenIteratorFactory, StaticDoctrineAnnotationParser $staticDoctrineAnnotationParser)
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
        $this->attributeNameFactory = $attributeNameFactory;
        $this->namedArgsFactory = $namedArgsFactory;
        $this->attributeArrayNameInliner = $attributeArrayNameInliner;
        $this->tokenIteratorFactory = $tokenIteratorFactory;
        $this->staticDoctrineAnnotationParser = $staticDoctrineAnnotationParser;
    }
    /**
     * @param Use_[] $uses
     */
    public function create(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, NestedAnnotationToAttribute $nestedAnnotationToAttribute, array $uses) : AttributeGroup
    {
        $values = $doctrineAnnotationTagValueNode->getValues();
        $values = $this->removeItems($values, $nestedAnnotationToAttribute);
        $args = $this->createArgsFromItems($values);
        $args = $this->attributeArrayNameInliner->inlineArrayToArgs($args);
        $attributeName = $this->attributeNameFactory->create($nestedAnnotationToAttribute, $doctrineAnnotationTagValueNode, $uses);
        $attribute = new Attribute($attributeName, $args);
        return new AttributeGroup([$attribute]);
    }
    /**
     * @return AttributeGroup[]
     */
    public function createNested(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, NestedAnnotationToAttribute $nestedAnnotationToAttribute) : array
    {
        $attributeGroups = [];
        if ($nestedAnnotationToAttribute->hasExplicitParameters()) {
            return $this->createFromExplicitProperties($nestedAnnotationToAttribute, $doctrineAnnotationTagValueNode);
        }
        $nestedAnnotationPropertyToAttributeClass = $nestedAnnotationToAttribute->getAnnotationPropertiesToAttributeClasses()[0];
        foreach ($doctrineAnnotationTagValueNode->values as $arrayItemNode) {
            $nestedDoctrineAnnotationTagValueNode = $arrayItemNode->value;
            if (!$nestedDoctrineAnnotationTagValueNode instanceof CurlyListNode) {
                continue;
            }
            foreach ($nestedDoctrineAnnotationTagValueNode->values as $nestedArrayItemNode) {
                if (!$nestedArrayItemNode->value instanceof DoctrineAnnotationTagValueNode) {
                    continue;
                }
                $attributeArgs = $this->createAttributeArgs($nestedArrayItemNode->value);
                $originalIdentifier = $doctrineAnnotationTagValueNode->identifierTypeNode->name;
                $attributeName = $this->resolveAliasedAttributeName($originalIdentifier, $nestedAnnotationPropertyToAttributeClass);
                $attribute = new Attribute($attributeName, $attributeArgs);
                $attributeGroups[] = new AttributeGroup([$attribute]);
            }
        }
        return $attributeGroups;
    }
    /**
     * @return Arg[]
     */
    private function createAttributeArgs(DoctrineAnnotationTagValueNode $nestedDoctrineAnnotationTagValueNode) : array
    {
        $args = $this->createArgsFromItems($nestedDoctrineAnnotationTagValueNode->getValues());
        return $this->attributeArrayNameInliner->inlineArrayToArgs($args);
    }
    /**
     * @param ArrayItemNode[] $arrayItemNodes
     * @return Arg[]
     */
    private function createArgsFromItems(array $arrayItemNodes) : array
    {
        $arrayItemNodes = $this->annotationToAttributeMapper->map($arrayItemNodes);
        $values = $arrayItemNodes instanceof Array_ ? $arrayItemNodes->items : $arrayItemNodes;
        return $this->namedArgsFactory->createFromValues($values);
    }
    /**
     * @todo improve this hardcoded approach later
     * @return \PhpParser\Node\Name\FullyQualified|\PhpParser\Node\Name
     */
    private function resolveAliasedAttributeName(string $originalIdentifier, AnnotationPropertyToAttributeClass $annotationPropertyToAttributeClass)
    {
        /** @var string $shortDoctrineAttributeName */
        $shortDoctrineAttributeName = Strings::after($annotationPropertyToAttributeClass->getAttributeClass(), '\\', -1);
        if (\strncmp($originalIdentifier, '@ORM', \strlen('@ORM')) === 0) {
            // or alias
            return new Name('ORM\\' . $shortDoctrineAttributeName);
        }
        // short alias
        if (\strpos($originalIdentifier, '\\') === \false) {
            return new Name($shortDoctrineAttributeName);
        }
        return new FullyQualified($annotationPropertyToAttributeClass->getAttributeClass());
    }
    /**
     * @param ArrayItemNode[] $arrayItemNodes
     * @return ArrayItemNode[]
     */
    private function removeItems(array $arrayItemNodes, NestedAnnotationToAttribute $nestedAnnotationToAttribute) : array
    {
        foreach ($nestedAnnotationToAttribute->getAnnotationPropertiesToAttributeClasses() as $annotationPropertyToAttributeClass) {
            foreach ($arrayItemNodes as $key => $arrayItemNode) {
                if ($arrayItemNode->key !== $annotationPropertyToAttributeClass->getAnnotationProperty()) {
                    continue;
                }
                unset($arrayItemNodes[$key]);
            }
        }
        return $arrayItemNodes;
    }
    /**
     * @return AttributeGroup[]
     */
    private function createFromExplicitProperties(NestedAnnotationToAttribute $nestedAnnotationToAttribute, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : array
    {
        $attributeGroups = [];
        foreach ($nestedAnnotationToAttribute->getAnnotationPropertiesToAttributeClasses() as $annotationPropertyToAttributeClass) {
            /** @var string $annotationProperty */
            $annotationProperty = $annotationPropertyToAttributeClass->getAnnotationProperty();
            $nestedArrayItemNode = $doctrineAnnotationTagValueNode->getValue($annotationProperty);
            if (!$nestedArrayItemNode instanceof ArrayItemNode) {
                continue;
            }
            if (!$nestedArrayItemNode->value instanceof CurlyListNode) {
                throw new ShouldNotHappenException();
            }
            foreach ($nestedArrayItemNode->value->getValues() as $arrayItemNode) {
                $nestedDoctrineAnnotationTagValueNode = $arrayItemNode->value;
                if (!$nestedDoctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
                    Assert::string($nestedDoctrineAnnotationTagValueNode);
                    $match = Strings::match($nestedDoctrineAnnotationTagValueNode, DoctrineAnnotationDecorator::LONG_ANNOTATION_REGEX);
                    if (!isset($match['class_name'])) {
                        throw new ShouldNotHappenException();
                    }
                    $identifierTypeNode = new IdentifierTypeNode($match['class_name']);
                    $identifierTypeNode->setAttribute(PhpDocAttributeKey::RESOLVED_CLASS, $match['class_name']);
                    $annotationContent = $match['annotation_content'] ?? '';
                    $nestedTokenIterator = $this->tokenIteratorFactory->create($annotationContent);
                    // mimics doctrine behavior just in phpdoc-parser syntax :)
                    // https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/lib/Doctrine/Common/Annotations/DocParser.php#L742
                    $values = $this->staticDoctrineAnnotationParser->resolveAnnotationMethodCall($nestedTokenIterator, new Nop());
                    $nestedDoctrineAnnotationTagValueNode = new DoctrineAnnotationTagValueNode($identifierTypeNode, $match['annotation_content'] ?? '', $values);
                }
                $attributeArgs = $this->createAttributeArgs($nestedDoctrineAnnotationTagValueNode);
                $originalIdentifier = $nestedDoctrineAnnotationTagValueNode->identifierTypeNode->name;
                $attributeName = $this->resolveAliasedAttributeName($originalIdentifier, $annotationPropertyToAttributeClass);
                if ($annotationPropertyToAttributeClass->doesNeedNewImport() && \count($attributeName->getParts()) === 1) {
                    $attributeName->setAttribute(AttributeKey::EXTRA_USE_IMPORT, $annotationPropertyToAttributeClass->getAttributeClass());
                }
                $attribute = new Attribute($attributeName, $attributeArgs);
                $attributeGroups[] = new AttributeGroup([$attribute]);
            }
        }
        return $attributeGroups;
    }
}
