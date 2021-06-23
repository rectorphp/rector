<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\Printer;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\ValueObject\AnnotationToAttribute;
final class PhpAttributeGroupFactory
{
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
        $fullyQualified = new \PhpParser\Node\Name\FullyQualified($annotationToAttribute->getAttributeClass());
        $values = $doctrineAnnotationTagValueNode->getValuesWithExplicitSilentAndWithoutQuotes();
        $args = $this->createArgsFromItems($values);
        $attribute = new \PhpParser\Node\Attribute($fullyQualified, $args);
        return new \PhpParser\Node\AttributeGroup([$attribute]);
    }
    /**
     * @param mixed[] $items
     * @return Arg[]
     */
    public function createArgsFromItems(array $items, ?string $silentKey = null) : array
    {
        $args = [];
        if ($silentKey !== null && isset($items[$silentKey])) {
            $silentValue = \PhpParser\BuilderHelpers::normalizeValue($items[$silentKey]);
            $this->normalizeStringDoubleQuote($silentValue);
            $args[] = new \PhpParser\Node\Arg($silentValue);
            unset($items[$silentKey]);
        }
        foreach ($items as $key => $value) {
            $value = $this->normalizeNodeValue($value);
            $value = \PhpParser\BuilderHelpers::normalizeValue($value);
            $this->normalizeStringDoubleQuote($value);
            $name = null;
            if (\is_string($key)) {
                $name = new \PhpParser\Node\Identifier($key);
            }
            $args[] = $this->isArrayArguments($items) ? new \PhpParser\Node\Arg($value, \false, \false, [], $name) : new \PhpParser\Node\Arg($value);
        }
        return $args;
    }
    /**
     * @param mixed[] $items
     */
    private function isArrayArguments(array $items) : bool
    {
        foreach (\array_keys($items) as $key) {
            if (!\is_int($key)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param mixed $value
     * @return bool|float|int|string|mixed[]|\PhpParser\Node\Expr
     */
    private function normalizeNodeValue($value)
    {
        if ($value instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode) {
            return (int) $value->value;
        }
        if ($value instanceof \PHPStan\Type\Constant\ConstantFloatType || $value instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
            return $value->getValue();
        }
        if ($value instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode) {
            return \true;
        }
        if ($value instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode) {
            return \false;
        }
        if ($value instanceof \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode) {
            return \array_map(function ($node) {
                return $this->normalizeNodeValue($node);
            }, $value->getValuesWithExplicitSilentAndWithoutQuotes());
        }
        if (\is_string($value) && \strpos($value, '::') !== \false) {
            // class const fetch
            [$class, $constant] = \explode('::', $value);
            return new \PhpParser\Node\Expr\ClassConstFetch(new \PhpParser\Node\Name($class), $constant);
        }
        if ($value instanceof \PHPStan\PhpDocParser\Ast\Node) {
            return (string) $value;
        }
        if (\is_array($value)) {
            return \array_map(function ($item) {
                return $this->normalizeNodeValue($item);
            }, $value);
        }
        return $value;
    }
    private function normalizeStringDoubleQuote(\PhpParser\Node\Expr $expr) : void
    {
        if (!$expr instanceof \PhpParser\Node\Scalar\String_) {
            return;
        }
        // avoid escaping quotes
        if (\strpos($expr->value, "'") === \false) {
            return;
        }
        $expr->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND, \PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED);
    }
}
