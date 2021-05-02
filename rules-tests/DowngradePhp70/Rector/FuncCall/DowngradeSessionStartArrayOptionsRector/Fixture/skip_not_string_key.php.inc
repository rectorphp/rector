<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector\Fixture;

class SkipNotStringKey
{
    public function start()
    {
        session_start([
            true => 'private',
        ]);
    }
}

?>

