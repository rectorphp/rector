<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

function regexFalsePositive()
{
    // keep
    preg_match("#^\d{4}-\d{2}-\d{2}$#", 'some text');
}
