<?php

declare(strict_types=1);

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
use Rector\PhpAttribute\NodeAnalyzer\ExprParameterReflectionTypeCorrector;
use Rector\PhpAttribute\NodeAnalyzer\NamedArgumentsResolver;
use Rector\PhpAttribute\NodeFactory\AttributeNameFactory;
use Rector\PhpAttribute\NodeFactory\NamedArgsFactory;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\PhpAttribute\Printer\PhpAttributeGroupFactoryTest
 */
final class PhpAttributeGroupFactory
{
    public function __construct(
        private readonly NamedArgumentsResolver $namedArgumentsResolver,
        private readonly AnnotationToAttributeMapper $annotationToAttributeMapper,
        private readonly AttributeNameFactory $attributeNameFactory,
        private readonly NamedArgsFactory $namedArgsFactory,
        private readonly ExprParameterReflectionTypeCorrector $exprParameterReflectionTypeCorrector
    ) {
    }

    public function createFromSimpleTag(AnnotationToAttribute $annotationToAttribute): AttributeGroup
    {
        return $this->createFromClass($annotationToAttribute->getAttributeClass());
    }

    public function createFromClass(string $attributeClass): AttributeGroup
    {
        $fullyQualified = new FullyQualified($attributeClass);
        $attribute = new Attribute($fullyQualified);
        return new AttributeGroup([$attribute]);
    }

    /**
     * @param mixed[] $items
     */
    public function createFromClassWithItems(string $attributeClass, array $items): AttributeGroup
    {
        $fullyQualified = new FullyQualified($attributeClass);
        $args = $this->createArgsFromItems($items, $attributeClass);
        $attribute = new Attribute($fullyQualified, $args);

        return new AttributeGroup([$attribute]);
    }

    public function create(
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode,
        AnnotationToAttribute $annotationToAttribute,
    ): AttributeGroup {
        $values = $doctrineAnnotationTagValueNode->getValuesWithExplicitSilentAndWithoutQuotes();

        $args = $this->createArgsFromItems($values, $annotationToAttribute->getAttributeClass());
        $argumentNames = $this->namedArgumentsResolver->resolveFromClass($annotationToAttribute->getAttributeClass());

        $this->completeNamedArguments($args, $argumentNames);

        $attributeName = $this->attributeNameFactory->create($annotationToAttribute, $doctrineAnnotationTagValueNode);

        $attribute = new Attribute($attributeName, $args);
        return new AttributeGroup([$attribute]);
    }

    /**
     * @param mixed[] $items
     * @return Arg[]
     */
    public function createArgsFromItems(array $items, string $attributeClass): array
    {
        /** @var Expr[] $items */
        $items = $this->annotationToAttributeMapper->map($items);

        $items = $this->exprParameterReflectionTypeCorrector->correctItemsByAttributeClass($items, $attributeClass);

        return $this->namedArgsFactory->createFromValues($items);
    }

    /**
     * @param Arg[] $args
     * @param string[] $argumentNames
     */
    private function completeNamedArguments(array $args, array $argumentNames): void
    {
        Assert::allIsAOf($args, Arg::class);

        foreach ($args as $key => $arg) {
            $argumentName = $argumentNames[$key] ?? null;
            if ($argumentName === null) {
                continue;
            }

            if ($arg->name !== null) {
                continue;
            }

            $arg->name = new Identifier($argumentName);
        }
    }
}
