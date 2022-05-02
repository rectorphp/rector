<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source;

class LocaleUtils
{
    public static function getAllLocales(): array
    {
        return true;
    }
}