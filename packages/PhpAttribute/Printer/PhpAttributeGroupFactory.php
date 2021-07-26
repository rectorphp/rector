<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\Printer;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\PhpAttribute\NodeAnalyzer\NamedArgumentsResolver;
use Rector\PhpAttribute\Value\ValueNormalizer;
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
     * @var \Rector\PhpAttribute\Value\ValueNormalizer
     */
    private $valueNormalizer;
    public function __construct(\Rector\PhpAttribute\NodeAnalyzer\NamedArgumentsResolver $namedArgumentsResolver, \Rector\PhpAttribute\Value\ValueNormalizer $valueNormalizer)
    {
        $this->namedArgumentsResolver = $namedArgumentsResolver;
        $this->valueNormalizer = $valueNormalizer;
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
        $fullyQualified = new \PhpParser\Node\Name\FullyQualified($annotationToAttribute->getAttributeClass());
        $values = $doctrineAnnotationTagValueNode->getValuesWithExplicitSilentAndWithoutQuotes();
        $args = $this->createArgsFromItems($values);
        $argumentNames = $this->namedArgumentsResolver->resolveFromClass($annotationToAttribute->getAttributeClass());
        $this->completeNamedArguments($args, $argumentNames);
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
            $value = $this->valueNormalizer->normalize($value);
            $value = \PhpParser\BuilderHelpers::normalizeValue($value);
            $this->normalizeStringDoubleQuote($value);
            $name = null;
            if (\is_string($key)) {
                $name = new \PhpParser\Node\Identifier($key);
            }
            // resolve argument name
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
    private function normalizeStringDoubleQuote(\PhpParser\Node\Expr $expr) : void
    {
        if (!$expr instanceof \PhpParser\Node\Scalar\String_) {
            return;
        }
        // avoid escaping quotes + preserve newlines
        if (\strpos($expr->value, "'") === \false) {
            return;
        }
        if (\strpos($expr->value, "\n") !== \false) {
            return;
        }
        $expr->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND, \PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED);
    }
    /**
     * @param Arg[] $args
     * @param string[] $argumentNames
     */
    private function completeNamedArguments(array $args, array $argumentNames) : void
    {
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
