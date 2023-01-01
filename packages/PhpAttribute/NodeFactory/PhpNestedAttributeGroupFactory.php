<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\NodeFactory;

use RectorPrefix202301\Nette\Utils\Strings;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\ValueObject\AnnotationPropertyToAttributeClass;
use Rector\Php80\ValueObject\NestedAnnotationToAttribute;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\AttributeArrayNameInliner;
use Rector\PhpAttribute\NodeAnalyzer\ExprParameterReflectionTypeCorrector;
final class PhpNestedAttributeGroupFactory
{
    /**
     * @var string
     * @see https://regex101.com/r/g3d9jy/1
     */
    private const SHORT_ORM_ALIAS_REGEX = '#^@ORM#';
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
    public function __construct(AnnotationToAttributeMapper $annotationToAttributeMapper, \Rector\PhpAttribute\NodeFactory\AttributeNameFactory $attributeNameFactory, \Rector\PhpAttribute\NodeFactory\NamedArgsFactory $namedArgsFactory, ExprParameterReflectionTypeCorrector $exprParameterReflectionTypeCorrector, AttributeArrayNameInliner $attributeArrayNameInliner)
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
        $this->attributeNameFactory = $attributeNameFactory;
        $this->namedArgsFactory = $namedArgsFactory;
        $this->exprParameterReflectionTypeCorrector = $exprParameterReflectionTypeCorrector;
        $this->attributeArrayNameInliner = $attributeArrayNameInliner;
    }
    /**
     * @param Use_[] $uses
     */
    public function create(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, NestedAnnotationToAttribute $nestedAnnotationToAttribute, array $uses) : AttributeGroup
    {
        $values = $doctrineAnnotationTagValueNode->getValues();
        $values = $this->removeItems($values, $nestedAnnotationToAttribute);
        $args = $this->createArgsFromItems($values, $nestedAnnotationToAttribute);
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
                $attributeArgs = $this->createAttributeArgs($nestedArrayItemNode->value, $nestedAnnotationToAttribute);
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
    private function createAttributeArgs(DoctrineAnnotationTagValueNode $nestedDoctrineAnnotationTagValueNode, NestedAnnotationToAttribute $nestedAnnotationToAttribute) : array
    {
        $args = $this->createArgsFromItems($nestedDoctrineAnnotationTagValueNode->getValues(), $nestedAnnotationToAttribute);
        return $this->attributeArrayNameInliner->inlineArrayToArgs($args);
    }
    /**
     * @param ArrayItemNode[] $arrayItemNodes
     * @return Arg[]
     */
    private function createArgsFromItems(array $arrayItemNodes, NestedAnnotationToAttribute $nestedAnnotationToAttribute) : array
    {
        /** @var Expr[]|Expr\Array_ $arrayItemNodes */
        $arrayItemNodes = $this->annotationToAttributeMapper->map($arrayItemNodes);
        $arrayItemNodes = $this->exprParameterReflectionTypeCorrector->correctItemsByAttributeClass($arrayItemNodes, $nestedAnnotationToAttribute->getTag());
        return $this->namedArgsFactory->createFromValues($arrayItemNodes);
    }
    /**
     * @todo improve this hardcoded approach later
     * @return \PhpParser\Node\Name\FullyQualified|\PhpParser\Node\Name
     */
    private function resolveAliasedAttributeName(string $originalIdentifier, AnnotationPropertyToAttributeClass $annotationPropertyToAttributeClass)
    {
        /** @var string $shortDoctrineAttributeName */
        $shortDoctrineAttributeName = Strings::after($annotationPropertyToAttributeClass->getAttributeClass(), '\\', -1);
        $matches = Strings::match($originalIdentifier, self::SHORT_ORM_ALIAS_REGEX);
        if ($matches !== null) {
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
                    throw new ShouldNotHappenException();
                }
                $attributeArgs = $this->createAttributeArgs($nestedDoctrineAnnotationTagValueNode, $nestedAnnotationToAttribute);
                $originalIdentifier = $nestedDoctrineAnnotationTagValueNode->identifierTypeNode->name;
                $attributeName = $this->resolveAliasedAttributeName($originalIdentifier, $annotationPropertyToAttributeClass);
                if ($annotationPropertyToAttributeClass->doesNeedNewImport() && \count($attributeName->parts) === 1) {
                    $attributeName->setAttribute(AttributeKey::EXTRA_USE_IMPORT, $annotationPropertyToAttributeClass->getAttributeClass());
                }
                $attribute = new Attribute($attributeName, $attributeArgs);
                $attributeGroups[] = new AttributeGroup([$attribute]);
            }
        }
        return $attributeGroups;
    }
}
