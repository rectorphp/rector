<?php

declare(strict_types=1);

namespace Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Source;

class WithOnlyStaticMethods
{
    public static function aBoolMethod():bool
    {
        return true;
    }

    public static function aStringMethod():string
    {
        return 'yeah';
    }
}
