<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\Printer;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Name;
use Rector\PhpAttribute\Contract\ManyPhpAttributableTagNodeInterface;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;

final class PhpAttributteGroupFactory
{
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

        $attributeGroups = [];
        $attributeGroups[] = $this->createAttributeGroupFromShortNameAndArgs(
            $phpAttributableTagNode->getShortName(),
            $args
        );

        if ($phpAttributableTagNode instanceof ManyPhpAttributableTagNodeInterface) {
            foreach ($phpAttributableTagNode->provide() as $shortName => $items) {
                $args = $this->createArgsFromItems($items);
                $attributeGroups[] = $this->createAttributeGroupFromShortNameAndArgs($shortName, $args);
            }
        }

        return $attributeGroups;
    }

    /**
     * @return Arg[]
     */
    private function createArgsFromItems(array $items): array
    {
        $args = [];

        if ($this->isArrayArguments($items)) {
            $arrayItems = [];
            foreach ($items as $key => $value) {
                $key = BuilderHelpers::normalizeValue($key);
                $value = BuilderHelpers::normalizeValue($value);
                $arrayItems[] = new ArrayItem($value, $key);
            }

            $args[] = new Arg(new Array_($arrayItems));
        } else {
            foreach ($items as $value) {
                $value = BuilderHelpers::normalizeValue($value);
                $args[] = new Arg($value);
            }
        }

        return $args;
    }

    /**
     * @param Arg[] $args
     */
    private function createAttributeGroupFromShortNameAndArgs(string $shortName, array $args): AttributeGroup
    {
        $attribute = new Attribute(new Name($shortName), $args);
        return new AttributeGroup([$attribute]);
    }

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
