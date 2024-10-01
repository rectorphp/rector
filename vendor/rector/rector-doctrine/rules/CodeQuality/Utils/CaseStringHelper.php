<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Utils;

use RectorPrefix202410\Nette\Utils\Strings;
final class CaseStringHelper
{
    public static function camelCase(string $value) : string
    {
        $spacedValue = \str_replace('_', ' ', $value);
        $uppercasedWords = \ucwords($spacedValue);
        $spacelessWords = \str_replace(' ', '', $uppercasedWords);
        $lowercasedValue = \lcfirst($spacelessWords);
        return Strings::replace($lowercasedValue, '#\\W#', '');
    }
}
