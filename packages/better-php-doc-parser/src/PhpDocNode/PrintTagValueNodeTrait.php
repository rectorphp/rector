<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode;

use Nette\Utils\Strings;

trait PrintTagValueNodeTrait
{
    protected function makeKeysExplicit(array $items): array
    {
        foreach ($items as $key => $contentItem) {
            if ($this->shouldSkipFromExplicitKey($contentItem, $key)) {
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

    protected function completeItemsQuotes(array $items): array
    {
        foreach ($items as $key => $item) {
            if (! is_string($item)) {
                continue;
            }

            // do not quote constant references
            if (Strings::match($item, '#\w+::\w+#')) {
                continue;
            }

            // no original quoting
            if ((isset($this->keysByQuotedStatus[$key]) && ! $this->keysByQuotedStatus[$key])) {
                continue;
            }

            $items[$key] = '"' . $item . '"';
        }

        return $items;
    }

    private function shouldSkipFromExplicitKey($contentItem, $key): bool
    {
        if (is_array($contentItem)) {
            return true;
        }

        if ($contentItem === null) {
            return true;
        }

        return $key === $this->silentKey && ! $this->isSilentKeyExplicit;
    }
}
