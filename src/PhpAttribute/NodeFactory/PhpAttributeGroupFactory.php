<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Use_;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php81\Enum\AttributeName;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\AttributeArrayNameInliner;
/**
 * @see \Rector\Tests\PhpAttribute\Printer\PhpAttributeGroupFactoryTest
 */
final class PhpAttributeGroupFactory
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
    public function __construct(AnnotationToAttributeMapper $annotationToAttributeMapper, \Rector\PhpAttribute\NodeFactory\AttributeNameFactory $attributeNameFactory, \Rector\PhpAttribute\NodeFactory\NamedArgsFactory $namedArgsFactory, AttributeArrayNameInliner $attributeArrayNameInliner)
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
        $this->attributeNameFactory = $attributeNameFactory;
        $this->namedArgsFactory = $namedArgsFactory;
        $this->attributeArrayNameInliner = $attributeArrayNameInliner;
    }
    public function createFromSimpleTag(AnnotationToAttribute $annotationToAttribute) : AttributeGroup
    {
        return $this->createFromClass($annotationToAttribute->getAttributeClass());
    }
    /**
     * @param AttributeName::*|string $attributeClass
     */
    public function createFromClass(string $attributeClass) : AttributeGroup
    {
        $fullyQualified = new FullyQualified($attributeClass);
        $attribute = new Attribute($fullyQualified);
        return new AttributeGroup([$attribute]);
    }
    /**
     * @api tests
     * @param mixed[] $items
     */
    public function createFromClassWithItems(string $attributeClass, array $items) : AttributeGroup
    {
        $fullyQualified = new FullyQualified($attributeClass);
        $args = $this->createArgsFromItems($items, $attributeClass);
        $attribute = new Attribute($fullyQualified, $args);
        return new AttributeGroup([$attribute]);
    }
    /**
     * @param Use_[] $uses
     */
    public function create(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, AnnotationToAttribute $annotationToAttribute, array $uses) : AttributeGroup
    {
        $values = $doctrineAnnotationTagValueNode->getValuesWithSilentKey();
        $args = $this->createArgsFromItems($values, $annotationToAttribute->getAttributeClass(), $annotationToAttribute->getClassReferenceFields());
        $args = $this->attributeArrayNameInliner->inlineArrayToArgs($args);
        $attributeName = $this->attributeNameFactory->create($annotationToAttribute, $doctrineAnnotationTagValueNode, $uses);
        // keep FQN in the attribute, so it can be easily detected later
        $attributeName->setAttribute(AttributeKey::PHP_ATTRIBUTE_NAME, $annotationToAttribute->getAttributeClass());
        $attribute = new Attribute($attributeName, $args);
        $attributeGroup = new AttributeGroup([$attribute]);
        $comment = $doctrineAnnotationTagValueNode->getAttribute(AttributeKey::ATTRIBUTE_COMMENT);
        if ($comment) {
            $attributeGroup->setAttribute(AttributeKey::ATTRIBUTE_COMMENT, $comment);
        }
        return $attributeGroup;
    }
    /**
     * @api tests
     *
     * @param ArrayItemNode[]|mixed[] $items
     * @param string[] $classReferencedFields
     * @return Arg[]
     */
    public function createArgsFromItems(array $items, string $attributeClass, array $classReferencedFields = []) : array
    {
        $mappedItems = $this->annotationToAttributeMapper->map($items);
        $this->mapClassReferences($mappedItems, $classReferencedFields);
        $values = $mappedItems instanceof Array_ ? $mappedItems->items : $mappedItems;
        // the key here should contain the named argument
        return $this->namedArgsFactory->createFromValues($values);
    }
    /**
     * @param string[] $classReferencedFields
     * @param \PhpParser\Node\Expr|string $expr
     */
    private function mapClassReferences($expr, array $classReferencedFields) : void
    {
        if (!$expr instanceof Array_) {
            return;
        }
        foreach ($expr->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$arrayItem->key instanceof String_) {
                continue;
            }
            if (!\in_array($arrayItem->key->value, $classReferencedFields)) {
                continue;
            }
            if ($arrayItem->value instanceof ClassConstFetch) {
                continue;
            }
            if (!$arrayItem->value instanceof String_) {
                continue;
            }
            $arrayItem->value = new ClassConstFetch(new FullyQualified($arrayItem->value->value), 'class');
        }
    }
}
