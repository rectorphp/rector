<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\Printer;

use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\NodeAnalyzer\NamedArgumentsResolver;
use Rector\PhpAttribute\NodeFactory\AttributeNameFactory;
use Rector\PhpAttribute\NodeFactory\NamedArgsFactory;
use RectorPrefix20211123\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\PhpAttribute\Printer\PhpAttributeGroupFactoryTest
 */
final class PhpAttributeGroupFactory
{
    /**
     * @var \Rector\PhpAttribute\NodeAnalyzer\NamedArgumentsResolver
     */
    private $namedArgumentsResolver;
    /**
     * @var \Rector\PhpAttribute\AnnotationToAttributeMapper
     */
    private $annotationToAttributeMapper;
    /**
     * @var \Rector\PhpAttribute\NodeFactory\AttributeNameFactory
     */
    private $attributeNameFactory;
    /**
     * @var \Rector\PhpAttribute\NodeFactory\NamedArgsFactory
     */
    private $namedArgsFactory;
    public function __construct(\Rector\PhpAttribute\NodeAnalyzer\NamedArgumentsResolver $namedArgumentsResolver, \Rector\PhpAttribute\AnnotationToAttributeMapper $annotationToAttributeMapper, \Rector\PhpAttribute\NodeFactory\AttributeNameFactory $attributeNameFactory, \Rector\PhpAttribute\NodeFactory\NamedArgsFactory $namedArgsFactory)
    {
        $this->namedArgumentsResolver = $namedArgumentsResolver;
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
        $this->attributeNameFactory = $attributeNameFactory;
        $this->namedArgsFactory = $namedArgsFactory;
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
        $args = $this->createArgsFromItems($items);
        $attribute = new \PhpParser\Node\Attribute($fullyQualified, $args);
        return new \PhpParser\Node\AttributeGroup([$attribute]);
    }
    public function create(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, \Rector\Php80\ValueObject\AnnotationToAttribute $annotationToAttribute) : \PhpParser\Node\AttributeGroup
    {
        $values = $doctrineAnnotationTagValueNode->getValuesWithExplicitSilentAndWithoutQuotes();
        $args = $this->createArgsFromItems($values);
        $argumentNames = $this->namedArgumentsResolver->resolveFromClass($annotationToAttribute->getAttributeClass());
        $this->completeNamedArguments($args, $argumentNames);
        $attributeName = $this->attributeNameFactory->create($annotationToAttribute, $doctrineAnnotationTagValueNode);
        $attribute = new \PhpParser\Node\Attribute($attributeName, $args);
        return new \PhpParser\Node\AttributeGroup([$attribute]);
    }
    /**
     * @param mixed[] $items
     * @return Arg[]
     */
    public function createArgsFromItems(array $items) : array
    {
        /** @var Expr[] $items */
        $items = $this->annotationToAttributeMapper->map($items);
        return $this->namedArgsFactory->createFromValues($items);
    }
    /**
     * @param Arg[] $args
     * @param string[] $argumentNames
     */
    private function completeNamedArguments(array $args, array $argumentNames) : void
    {
        \RectorPrefix20211123\Webmozart\Assert\Assert::allIsAOf($args, \PhpParser\Node\Arg::class);
        foreach ($args as $key => $arg) {
            $argumentName = $argumentNames[$key] ?? null;
            if ($argumentName === null) {
                continue;
            }
            if ($arg->name !== null) {
                continue;
            }
            $arg->name = new \PhpParser\Node\Identifier($argumentName);
        }
    }
}
