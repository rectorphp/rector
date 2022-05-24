<?php

namespace Rector\Tests\Php73\Rector\FuncCall\StringifyStrNeedlesRector\Fixture;

function skipEncapsed()
{
    $str = 'bar';
    strpos('foo bar', " {$str}");
}
