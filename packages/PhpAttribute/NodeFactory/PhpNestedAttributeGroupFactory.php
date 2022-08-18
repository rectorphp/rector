<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\NodeFactory;

use RectorPrefix202208\Nette\Utils\Strings;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
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
        $values = $doctrineAnnotationTagValueNode->getValuesWithExplicitSilentAndWithoutQuotes();
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
        $values = $doctrineAnnotationTagValueNode->getValuesWithExplicitSilentAndWithoutQuotes();
        foreach ($nestedAnnotationToAttribute->getAnnotationPropertiesToAttributeClasses() as $itemName => $nestedAttributeClass) {
            $nestedValues = $values[$itemName] ?? null;
            if ($nestedValues === null) {
                continue;
            }
            if ($nestedValues instanceof CurlyListNode) {
                foreach ($nestedValues->getValues() as $nestedDoctrineAnnotationTagValueNode) {
                    /** @var DoctrineAnnotationTagValueNode $nestedDoctrineAnnotationTagValueNode */
                    $args = $this->createArgsFromItems($nestedDoctrineAnnotationTagValueNode->getValuesWithExplicitSilentAndWithoutQuotes(), $nestedAnnotationToAttribute);
                    $args = $this->attributeArrayNameInliner->inlineArrayToArgs($args);
                    $originalIdentifier = $nestedDoctrineAnnotationTagValueNode->identifierTypeNode->name;
                    $attributeName = $this->resolveAliasedAttributeName($originalIdentifier, $nestedAttributeClass);
                    $attribute = new Attribute($attributeName, $args);
                    $attributeGroups[] = new AttributeGroup([$attribute]);
                }
            }
        }
        return $attributeGroups;
    }
    /**
     * @param mixed[] $items
     * @return Arg[]
     */
    private function createArgsFromItems(array $items, NestedAnnotationToAttribute $nestedAnnotationToAttribute) : array
    {
        // remove nested items
        foreach (\array_keys($nestedAnnotationToAttribute->getAnnotationPropertiesToAttributeClasses()) as $itemName) {
            unset($items[$itemName]);
        }
        /** @var Expr[]|Expr\Array_ $items */
        $items = $this->annotationToAttributeMapper->map($items);
        $items = $this->exprParameterReflectionTypeCorrector->correctItemsByAttributeClass($items, $nestedAnnotationToAttribute->getTag());
        return $this->namedArgsFactory->createFromValues($items);
    }
    /**
     * @todo improve this hardcoded approach later
     * @return \PhpParser\Node\Name\FullyQualified|\PhpParser\Node\Name
     */
    private function resolveAliasedAttributeName(string $originalIdentifier, string $nestedAttributeClass)
    {
        $matches = Strings::match($originalIdentifier, self::SHORT_ORM_ALIAS_REGEX);
        if ($matches !== null) {
            // or alias
            $shortDoctrineAttributeName = Strings::after($nestedAttributeClass, '\\', -1);
            return new Name('ORM\\' . $shortDoctrineAttributeName);
        }
        return new FullyQualified($nestedAttributeClass);
    }
}
