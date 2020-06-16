<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObjectFactory;

use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\Utils\ArrayItemStaticHelper;
use Rector\BetterPhpDocParser\ValueObject\TagValueNodeConfiguration;

final class TagValueNodeConfigurationFactory
{
    public function createFromOriginalContent(?string $originalContent, ?string $silentKey): TagValueNodeConfiguration
    {
        if ($originalContent === null) {
            return new TagValueNodeConfiguration();
        }

        $orderedVisibleItems = ArrayItemStaticHelper::resolveAnnotationItemsOrder($originalContent, $silentKey);

        $hasNewlineAfterOpening = (bool) Strings::match($originalContent, '#^(\(\s+|\n)#m');
        $hasNewlineBeforeClosing = (bool) Strings::match($originalContent, '#(\s+\)|\n(\s+)?)$#m');

        $hasOpeningBracket = (bool) Strings::match($originalContent, '#^\(#');
        $hasClosingBracket = (bool) Strings::match($originalContent, '#\)$#');

        $keysByQuotedStatus = [];
        foreach ($orderedVisibleItems as $orderedVisibleItem) {
            $keysByQuotedStatus[$orderedVisibleItem] = $this->isKeyQuoted(
                $originalContent,
                $orderedVisibleItem,
                $silentKey
            );
        }

        $isSilentKeyExplicit = (bool) Strings::contains($originalContent, sprintf('%s=', $silentKey));

        return new TagValueNodeConfiguration(
            $originalContent,
            $orderedVisibleItems,
            $hasNewlineAfterOpening,
            $hasNewlineBeforeClosing,
            $hasOpeningBracket,
            $hasClosingBracket,
            $keysByQuotedStatus,
            $silentKey,
            $isSilentKeyExplicit
        );
    }

    private function isKeyQuoted(string $originalContent, string $key, ?string $silentKey): bool
    {
        $escapedKey = preg_quote($key, '#');

        $quotedKeyPattern = $this->createQuotedKeyPattern($silentKey, $key, $escapedKey);
        if ((bool) Strings::match($originalContent, $quotedKeyPattern)) {
            return true;
        }

        // @see https://regex101.com/r/VgvK8C/5/
        $quotedArrayPattern = sprintf('#%s=\{"(.*)"\}|\{"(.*)"\}#', $escapedKey);

        return (bool) Strings::match($originalContent, $quotedArrayPattern);
    }

    private function createQuotedKeyPattern(?string $silentKey, string $key, string $escapedKey): string
    {
        if ($silentKey === $key) {
            // @see https://regex101.com/r/VgvK8C/4/
            return sprintf('#(%s=")|\("#', $escapedKey);
        }

        // @see https://regex101.com/r/VgvK8C/3/
        return sprintf('#%s="#', $escapedKey);
    }
}
