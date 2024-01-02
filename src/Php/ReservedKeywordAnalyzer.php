<?php

declare (strict_types=1);
namespace Rector\Php;

final class ReservedKeywordAnalyzer
{
    /**
     * @var string[]
     */
    private const NATIVE_VARIABLE_NAMES = ['_ENV', '_POST', '_GET', '_COOKIE', '_SERVER', '_FILES', '_REQUEST', '_SESSION', 'GLOBALS'];
    public function isNativeVariable(string $name) : bool
    {
        return \in_array($name, self::NATIVE_VARIABLE_NAMES, \true);
    }
}
