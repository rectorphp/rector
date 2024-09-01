<?php

declare (strict_types=1);
namespace Rector\Util;

use RectorPrefix202409\Nette\Utils\Strings;
final class StringUtils
{
    public static function isMatch(string $value, string $regex) : bool
    {
        $match = Strings::match($value, $regex);
        return $match !== null;
    }
}
