<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\Printer;

use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\AttributeArrayNameInliner;
use Rector\PhpAttribute\NodeAnalyzer\ExprParameterReflectionTypeCorrector;
use Rector\PhpAttribute\NodeFactory\AttributeNameFactory;
use Rector\PhpAttribute\NodeFactory\NamedArgsFactory;
/**
 * @see \Rector\Tests\PhpAttribute\Printer\PhpAttributeGroupFactoryTest
 */
final class PhpAttributeGroupFactory
{
    /**
     * @var array<string, string[]>>
     */
    private $unwrappedAnnotations = [];
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
     * @var \Rector\PhpAttribute\NodeAnalyzer\ExprParameterReflectionTypeCorrector
     */
    private $exprParameterReflectionTypeCorrector;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\AttributeArrayNameInliner
     */
    private $attributeArrayNameInliner;
    public function __construct(\Rector\PhpAttribute\AnnotationToAttributeMapper $annotationToAttributeMapper, \Rector\PhpAttribute\NodeFactory\AttributeNameFactory $attributeNameFactory, \Rector\PhpAttribute\NodeFactory\NamedArgsFactory $namedArgsFactory, \Rector\PhpAttribute\NodeAnalyzer\ExprParameterReflectionTypeCorrector $exprParameterReflectionTypeCorrector, \Rector\PhpAttribute\AttributeArrayNameInliner $attributeArrayNameInliner, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
        $this->attributeNameFactory = $attributeNameFactory;
        $this->namedArgsFactory = $namedArgsFactory;
        $this->exprParameterReflectionTypeCorrector = $exprParameterReflectionTypeCorrector;
        $this->attributeArrayNameInliner = $attributeArrayNameInliner;
        // nested indexes supported only since PHP 8.1
        if (!$phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::NEW_INITIALIZERS)) {
            $this->unwrappedAnnotations['Doctrine\\ORM\\Mapping\\Table'] = ['indexes', 'uniqueConstraints'];
            $this->unwrappedAnnotations['Doctrine\\ORM\\Mapping\\Entity'][] = 'uniqueConstraints';
        }
    }
    public function createFromSimpleTag(\Rector\Php80\ValueObject\AnnotationToAttribute $annotationToAttribute) : \PhpParser\Node\AttributeGroup
    {
        return $this->createFromClass($annotationToAttribute->getAttributeClass());
    }
    public function createFromClass(string $attributeClass) : \PhpParser\Node\AttributeGroup
    {
        $fullyQualified = new \PhpParser\Node\Name\FullyQualified($attributeClass);
        $attribute = new \PhpParser\Node\Attribute($fullyQualified);
        return new \PhpParser\Node\AttributeGroup([$attribute]);
    }
    /**
     * @param mixed[] $items
     */
    public function createFromClassWithItems(string $attributeClass, array $items) : \PhpParser\Node\AttributeGroup
    {
        $fullyQualified = new \PhpParser\Node\Name\FullyQualified($attributeClass);
        $args = $this->createArgsFromItems($items, $attributeClass);
        $attribute = new \PhpParser\Node\Attribute($fullyQualified, $args);
        return new \PhpParser\Node\AttributeGroup([$attribute]);
    }
    public function create(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, \Rector\Php80\ValueObject\AnnotationToAttribute $annotationToAttribute) : \PhpParser\Node\AttributeGroup
    {
        $values = $doctrineAnnotationTagValueNode->getValuesWithExplicitSilentAndWithoutQuotes();
        $args = $this->createArgsFromItems($values, $annotationToAttribute->getAttributeClass());
        // @todo this can be a different class then the unwrapped crated one
        //$argumentNames = $this->namedArgumentsResolver->resolveFromClass($annotationToAttribute->getAttributeClass());
        $args = $this->attributeArrayNameInliner->inlineArrayToArgs($args);
        $attributeName = $this->attributeNameFactory->create($annotationToAttribute, $doctrineAnnotationTagValueNode);
        $attribute = new \PhpParser\Node\Attribute($attributeName, $args);
        return new \PhpParser\Node\AttributeGroup([$attribute]);
    }
    /**
     * @param mixed[] $items
     * @return Arg[]
     */
    public function createArgsFromItems(array $items, string $attributeClass) : array
    {
        /** @var Expr[]|Expr\Array_ $items */
        $items = $this->annotationToAttributeMapper->map($items);
        $items = $this->exprParameterReflectionTypeCorrector->correctItemsByAttributeClass($items, $attributeClass);
        $items = $this->removeUnwrappedItems($attributeClass, $items);
        return $this->namedArgsFactory->createFromValues($items);
    }
    /**
     * @param mixed[] $items
     * @return mixed[]
     */
    private function removeUnwrappedItems(string $attributeClass, array $items) : array
    {
        // unshift annotations that can be extracted
        $unwrappeColumns = $this->unwrappedAnnotations[$attributeClass] ?? [];
        if ($unwrappeColumns === []) {
            return $items;
        }
        foreach ($items as $key => $item) {
            if (!$item instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            $arrayItemKey = $item->key;
            if (!$arrayItemKey instanceof \PhpParser\Node\Scalar\String_) {
                continue;
            }
            if (!\in_array($arrayItemKey->value, $unwrappeColumns, \true)) {
                continue;
            }
            unset($items[$key]);
        }
        return $items;
    }
}
