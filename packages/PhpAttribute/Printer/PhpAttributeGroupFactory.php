<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\Printer;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Php80\ValueObject\AnnotationToAttribute;

final class PhpAttributeGroupFactory
{
    public function createFromSimpleTag(AnnotationToAttribute $annotationToAttribute): AttributeGroup
    {
        $fullyQualified = new FullyQualified($annotationToAttribute->getAttributeClass());
        $attribute = new Attribute($fullyQualified);
        return new AttributeGroup([$attribute]);
    }

    public function create(
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode,
        AnnotationToAttribute $annotationToAttribute
    ): AttributeGroup {
        $fullyQualified = new FullyQualified($annotationToAttribute->getAttributeClass());

        $values = $doctrineAnnotationTagValueNode->getValuesWithExplicitSilentAndWithoutQuotes();
        $args = $this->createArgsFromItems($values);

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

                $value = $this->normalizeNodeValue($value);

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

    /**
     * @param mixed $value
     * @return bool|float|int|string
     */
    private function normalizeNodeValue($value)
    {
        if ($value instanceof ConstExprIntegerNode) {
            return (int) $value->value;
        }

        if ($value instanceof ConstantFloatType) {
            return (float) $value->getValue();
        }

        if ($value instanceof ConstantBooleanType) {
            return (bool) $value->getValue();
        }

        if ($value instanceof Node) {
            return (string) $value;
        }

        return $value;
    }
}
