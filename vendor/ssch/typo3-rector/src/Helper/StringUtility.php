<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Helper;

use function RectorPrefix20220531\Symfony\Component\String\u;
final class StringUtility
{
    public static function prepareExtensionName(string $extensionName, int $delimiterPosition) : string
    {
        $extensionName = \substr($extensionName, $delimiterPosition + 1);
        $stringy = \RectorPrefix20220531\Symfony\Component\String\u($extensionName);
        $underscores = $stringy->snake();
        $lower = $underscores->lower();
        $underScoredExtensionName = \str_replace('_', ' ', $lower->toString());
        $stringy = \RectorPrefix20220531\Symfony\Component\String\u($underScoredExtensionName);
        $trimmed = $stringy->trim();
        $uppercase = $trimmed->title();
        $underScoredExtensionName = \ucwords($uppercase->toString());
        return \str_replace(' ', '', $underScoredExtensionName);
    }
    /**
     * Check if an item exists in a comma-separated list of items.
     *
     * @param string $list Comma-separated list of items (string)
     * @param string $item Item to check for
     * @return bool TRUE if $item is in $list
     */
    public static function inList(string $list, string $item) : bool
    {
        return \strpos(',' . $list . ',', ',' . $item . ',') !== \false;
    }
}
