<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Helper;

use function RectorPrefix20220209\Symfony\Component\String\u;
final class StringUtility
{
    public static function prepareExtensionName(string $extensionName, int $delimiterPosition) : string
    {
        $extensionName = \substr($extensionName, $delimiterPosition + 1);
        $stringy = \RectorPrefix20220209\Symfony\Component\String\u($extensionName);
        $underscores = $stringy->snake();
        $lower = $underscores->lower();
        $underScoredExtensionName = \str_replace('_', ' ', $lower->toString());
        $stringy = \RectorPrefix20220209\Symfony\Component\String\u($underScoredExtensionName);
        $trimmed = $stringy->trim();
        $uppercase = $trimmed->title();
        $underScoredExtensionName = \ucwords($uppercase->toString());
        return \str_replace(' ', '', $underScoredExtensionName);
    }
}
