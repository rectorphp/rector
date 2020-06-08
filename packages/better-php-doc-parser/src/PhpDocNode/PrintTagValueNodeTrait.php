<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode;

use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;

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

    /**
     * @param string[] $skipKeys
     */
    protected function completeItemsQuotes(array $items, array $skipKeys = []): array
    {
        foreach ($items as $key => $item) {
            if (! is_string($item)) {
                continue;
            }

            if (in_array($key, $skipKeys, true)) {
                continue;
            }

            // do not quote constant references... unless twig template
            if (Strings::match($item, '#\w+::\w+#') && ! Strings::endsWith($item, '.twig')) {
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

        if (! $this instanceof SilentKeyNodeInterface) {
            return false;
        }

        /** @var SilentKeyNodeInterface&AbstractTagValueNode $this */
        if ($key !== $this->getSilentKey()) {
            return false;
        }

        return ! $this->isSilentKeyExplicit;
    }
}
