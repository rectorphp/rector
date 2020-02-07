<?php

namespace Rector\Laravel\Tests\Rector\StaticCall\MinutesToSecondsInCacheRector\Fixture;

use DateTime;

class SkipCall
{
    public function run()
    {
        \Illuminate\Support\Facades\Cache::put('key', 'value', new DateTime());
    }
}
