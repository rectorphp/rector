<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector\Fixture;

class SkipNotScalarValue
{
    public function start()
    {
        session_start([
            'cache_limiter' => new \stdClass(),
        ]);
    }
}

?>
