<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\ValueObject\TagValueNodeConfiguration;

final class TagValueNodePrinter
{
    /**
     * @var string
     * @see https://regex101.com/r/Krp6Jz/1
     */
    private const CONSTANT_REFERENCE_REGEX = '#\w+::\w+#';

    /**
     * @param mixed[] $items
     * @return mixed[]
     */
    public function makeKeysExplicit(array $items, TagValueNodeConfiguration $tagValueNodeConfiguration): array
    {
        foreach ($items as $key => $contentItem) {
            if ($this->shouldSkipFromExplicitKey($contentItem, $key, $tagValueNodeConfiguration)) {
                continue;
            }

            // boolish keys
            if ($key && is_bool($contentItem)) {
                $contentItem = $contentItem ? 'true' : 'false';
            }

            $items[$key] = $key . '=' . $contentItem;
        }

        return $items;
    }

    /**
     * @param mixed[] $items
     * @param string[] $skipKeys
     * @return mixed[]
     */
    public function completeItemsQuotes(
        TagValueNodeConfiguration $tagValueNodeConfiguration,
        array $items,
        array $skipKeys = []): array
    {
        foreach ($items as $key => $item) {
            if (! is_string($item)) {
                continue;
            }

            if (in_array($key, $skipKeys, true)) {
                continue;
            }

            // do not quote constant references... unless twig template
            if (Strings::match($item, self::CONSTANT_REFERENCE_REGEX) && ! Strings::endsWith($item, '.twig')) {
                continue;
            }

            // no original quoting
            $keysByQuotedStatus = $tagValueNodeConfiguration->getKeysByQuotedStatus();
            if (isset($keysByQuotedStatus[$key]) && ! $keysByQuotedStatus[$key]) {
                continue;
            }

            $items[$key] = '"' . $item . '"';
        }

        return $items;
    }

    /**
     * @param mixed $contentItem
     */
    private function shouldSkipFromExplicitKey(
        $contentItem,
        string $key,
        TagValueNodeConfiguration $tagValueNodeConfiguration
    ): bool {
        if (is_array($contentItem)) {
            return true;
        }

        if ($contentItem === null) {
            return true;
        }

        return $tagValueNodeConfiguration->isSilentKeyAndImplicit($key);
    }
}
