<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector\Fixture;

class SkipObjectValue
{
    public function start()
    {
        session_start([
            'cache_limiter' => new \stdClass(),
        ]);
    }
}

?>
