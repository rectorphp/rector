<?php

namespace Rector\Tests\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector\Fixture;

class OnlyOne
{
    public function run()
    {
        \Illuminate\Support\Facades\Cache::put('key', 'value', 1);
    }
}

?>
-----
<?php

namespace Rector\Tests\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector\Fixture;

class OnlyOne
{
    public function run()
    {
        \Illuminate\Support\Facades\Cache::put('key', 'value', 60);
    }
}

?>
