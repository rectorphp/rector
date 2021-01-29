<?php

namespace Rector\Removing\Tests\Rector\FuncCall\RemoveFuncCallRector\Fixture;

ini_get('bla');
ini_set('y2kcompliance', 0);
ini_fet('y2kcompliance', 0);

function ini_fet($key, $value)
{
}
