<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector\Fixture;

function SkipNotArrayOptions()
{
    session_start(true);
}

?>
