<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class SkipRegexDatetime
{
    public function run()
    {
        // keep
        preg_match("#^\d{4}-\d{2}-\d{2}$#", 'some text');
    }
}
