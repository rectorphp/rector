<?php

namespace Rector\Tests\Php73\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class SkipRegexDatetime
{
    public function run()
    {
        // keep
        preg_match("#^\d{4}-\d{2}-\d{2}$#", 'some text');
    }
}
