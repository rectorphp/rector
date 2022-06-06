<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Util;

use RectorPrefix20220606\Nette\Utils\Strings;
final class StringUtils
{
    public static function isMatch(string $value, string $regex) : bool
    {
        $match = Strings::match($value, $regex);
        return $match !== null;
    }
}
