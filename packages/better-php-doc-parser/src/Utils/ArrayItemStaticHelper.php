<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Utils;

use Nette\Utils\Strings;

/**
 * Helpers class for ordering items in values objects on call.
 * Beware of static methods as they might doom you on the edge of life.
 */
final class ArrayItemStaticHelper
{
    /**
     * @return string[]
     */
    public static function resolveAnnotationItemsOrder(string $content, ?string $silentKey = null): array
    {
        $itemsOrder = [];

        $matches = Strings::matchAll($content, '#(?<item>\w+)=#m');
        foreach ($matches as $match) {
            $itemsOrder[] = $match['item'];
        }

        // is not empty and has silent key
        if (self::isNotEmptyAndHasSilentKey($content, $silentKey, $itemsOrder)) {
            $itemsOrder = array_merge([$silentKey], $itemsOrder);
        }

        return $itemsOrder;
    }

    /**
     * @param string[] $contentItems
     * @param string[] $orderedVisibleItems
     * @return string[]
     */
    public static function filterAndSortVisibleItems(array $contentItems, array $orderedVisibleItems): array
    {
        // 1. remove unused items
        foreach (array_keys($contentItems) as $key) {
            // generic key
            if (is_int($key)) {
                continue;
            }

            if (in_array($key, $orderedVisibleItems, true)) {
                continue;
            }

            unset($contentItems[$key]);
        }

        return self::sortItemsByOrderedListOfKeys($contentItems, $orderedVisibleItems);
    }

    /**
     * 2. sort item by prescribed key order
     * @see https://www.designcise.com/web/tutorial/how-to-sort-an-array-by-keys-based-on-order-in-a-secondary-array-in-php
     * @param string[] $contentItems
     * @param string[] $orderedVisibleItems
     * @return string[]
     */
    private static function sortItemsByOrderedListOfKeys(array $contentItems, array $orderedVisibleItems): array
    {
        uksort($contentItems, function ($firstContentItem, $secondContentItem) use ($orderedVisibleItems): int {
            $firstItemPosition = array_search($firstContentItem, $orderedVisibleItems, true);
            $secondItemPosition = array_search($secondContentItem, $orderedVisibleItems, true);

            return $firstItemPosition <=> $secondItemPosition;
        });

        return $contentItems;
    }

    private static function isNotEmptyAndHasSilentKey(string $content, ?string $silentKey, array $itemsOrder): bool
    {
        if (! Strings::match($content, '#()|\(\)#')) {
            return false;
        }

        if ($silentKey === null) {
            return false;
        }

        return ! in_array($silentKey, $itemsOrder, true);
    }
}
