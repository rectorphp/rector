<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\Printer;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\PhpAttribute\Contract\ManyPhpAttributableTagNodeInterface;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;

final class PhpAttributeGroupFactory
{
    /**
     * A dummy placeholder for annotation, that we know will be converted to attributes,
     * but don't have specific attribute class yet.
     *
     * @var string
     */
    public const TO_BE_ANNOUNCED = 'TBA';

    /**
     * @param PhpAttributableTagNodeInterface[] $phpAttributableTagNodes
     * @return AttributeGroup[]
     */
    public function create(array $phpAttributableTagNodes): array
    {
        $attributeGroups = [];
        foreach ($phpAttributableTagNodes as $phpAttributableTagNode) {
            $currentAttributeGroups = $this->printPhpAttributableTagNode($phpAttributableTagNode);
            $attributeGroups = array_merge($attributeGroups, $currentAttributeGroups);
        }

        return $attributeGroups;
    }

    /**
     * @return Arg[]
     */
    public function printItemsToAttributeArgs(PhpAttributableTagNodeInterface $phpAttributableTagNode): array
    {
        $items = $phpAttributableTagNode->getAttributableItems();
        return $this->createArgsFromItems($items);
    }

    /**
     * @return AttributeGroup[]
     */
    private function printPhpAttributableTagNode(PhpAttributableTagNodeInterface $phpAttributableTagNode): array
    {
        $args = $this->printItemsToAttributeArgs($phpAttributableTagNode);

        $attributeClassName = $this->resolveAttributeClassName($phpAttributableTagNode);

        $attributeGroups = [];
        $attributeGroups[] = $this->createAttributeGroupFromNameAndArgs($attributeClassName, $args);

        if ($phpAttributableTagNode instanceof ManyPhpAttributableTagNodeInterface) {
            foreach ($phpAttributableTagNode->provide() as $shortName => $items) {
                $args = $this->createArgsFromItems($items);
                $name = new Name($shortName);
                $attributeGroups[] = $this->createAttributeGroupFromNameAndArgs($name, $args);
            }
        }

        return $attributeGroups;
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
            foreach ($items as $value) {
                $value = BuilderHelpers::normalizeValue($value);
                $args[] = new Arg($value);
            }
        }

        return $args;
    }

    private function resolveAttributeClassName(PhpAttributableTagNodeInterface $phpAttributableTagNode): Name
    {
        if ($phpAttributableTagNode->getAttributeClassName() !== self::TO_BE_ANNOUNCED) {
            return new FullyQualified($phpAttributableTagNode->getAttributeClassName());
        }

        return new Name($phpAttributableTagNode->getShortName());
    }

    /**
     * @param Arg[] $args
     */
    private function createAttributeGroupFromNameAndArgs(Name $name, array $args): AttributeGroup
    {
        $attribute = new Attribute($name, $args);
        return new AttributeGroup([$attribute]);
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
