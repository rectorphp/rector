<?php

declare (strict_types=1);
namespace Rector\Symfony\Utils;

final class StringUtils
{
    public static function underscoreToCamelCase(string $value) : string
    {
        $value = \ucwords(\str_replace(['-', '_'], ' ', $value));
        $singleWord = \str_replace(' ', '', $value);
        return \lcfirst($singleWord);
    }
}
