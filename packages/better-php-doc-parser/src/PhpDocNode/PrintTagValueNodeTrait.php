<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode;

use Nette\Utils\Strings;

trait PrintTagValueNodeTrait
{
    protected function makeKeysExplicit(array $items): array
    {
        foreach ($items as $key => $contentItem) {
            if (is_array($contentItem)) {
                continue;
            }

            if ($key === $this->silentKey && ! $this->isSilentKeyExplicit) {
                continue;
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

            $items[$key] = '"' . $item . '"';
        }

        return $items;
    }
}
