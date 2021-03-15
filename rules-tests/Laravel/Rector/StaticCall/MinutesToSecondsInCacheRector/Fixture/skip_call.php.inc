<?php

namespace Rector\Tests\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector\Fixture;

use DateTime;

class SkipCall
{
    public function run()
    {
        \Illuminate\Support\Facades\Cache::put('key', 'value', new DateTime());
    }
}
