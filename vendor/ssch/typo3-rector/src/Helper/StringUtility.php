<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Helper;

use UnexpectedValueException;
final class StringUtility
{
    public static function prepareExtensionName(string $extensionName, int $delimiterPosition) : string
    {
        $extensionName = \substr($extensionName, $delimiterPosition + 1);
        $underScoredExtensionName = \preg_replace('#[A-Z]#', 'RectorPrefix20210518\\_', \lcfirst($extensionName));
        if (!\is_string($underScoredExtensionName)) {
            throw new \UnexpectedValueException('The extension name could not be parsed');
        }
        return \str_replace(' ', '', \ucwords(\str_replace('_', ' ', \strtolower($underScoredExtensionName))));
    }
}
