<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\Printer;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\PhpDocParser\Ast\Node;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;
use Rector\Php80\ValueObject\AnnotationToAttribute;

final class PhpAttributeGroupFactory
{
    public function createFromSimpleTag(AnnotationToAttribute $annotationToAttribute): AttributeGroup
    {
        $fullyQualified = new FullyQualified($annotationToAttribute->getAttributeClass());
        $attribute = new Attribute($fullyQualified);
        return new AttributeGroup([$attribute]);
    }

    public function create(Node $node, AnnotationToAttribute $annotationToAttribute): AttributeGroup
    {
        $fullyQualified = new FullyQualified($annotationToAttribute->getAttributeClass());

        if ($node instanceof AbstractTagValueNode) {
            if ($node instanceof DoctrineAnnotationTagValueNode) {
                $values = $node->getValuesWithExplicitSilentAndWithoutQuotes();
            } else {
                $values = $node->getItemsWithoutDefaults();
            }

            $args = $this->createArgsFromItems($values);
        } else {
            $args = [];
        }

        $attribute = new Attribute($fullyQualified, $args);
        return new AttributeGroup([$attribute]);
    }

    /**
     * @param mixed[] $items
     * @return Arg[]
     */
    private function createArgsFromItems(array $items, ?string $silentKey = null): array
    {
        $args = [];

        if ($silentKey !== null && isset($items[$silentKey])) {
            $silentValue = BuilderHelpers::normalizeValue($items[$silentKey]);
            $args[] = new Arg($silentValue);
            unset($items[$silentKey]);
        }

        if ($this->isArrayArguments($items)) {
            foreach ($items as $key => $value) {
                $argumentName = new Identifier($key);
                $value = BuilderHelpers::normalizeValue($value);
                $args[] = new Arg($value, false, false, [], $argumentName);
            }
        } else {
            foreach ($items as $item) {
                $item = BuilderHelpers::normalizeValue($item);
                $args[] = new Arg($item);
            }
        }

        return $args;
    }

    /**
     * @param mixed[] $items
     */
    private function isArrayArguments(array $items): bool
    {
        foreach (array_keys($items) as $key) {
            if (! is_int($key)) {
                return true;
            }
        }

        return false;
    }
}
