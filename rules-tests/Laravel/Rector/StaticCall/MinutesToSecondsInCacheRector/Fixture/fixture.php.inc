<?php

namespace Rector\Tests\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector\Fixture;

use Illuminate\Contracts\Cache\Store;

class SomeClass
{
    /**
     * @var Store
     */
    private $arrayStore;

    public function __construct(Store $arrayStore)
    {
        $this->arrayStore = $arrayStore;
    }

    public function run()
    {
        \Illuminate\Support\Facades\Cache::put('key', 'value', 60);

        $this->arrayStore->put('key', 'value', 60);

        $seconds = 60;
        $this->arrayStore->putMany(['key' => 'value'], $seconds);
    }
}

?>
-----
<?php

namespace Rector\Tests\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector\Fixture;

use Illuminate\Contracts\Cache\Store;

class SomeClass
{
    /**
     * @var Store
     */
    private $arrayStore;

    public function __construct(Store $arrayStore)
    {
        $this->arrayStore = $arrayStore;
    }

    public function run()
    {
        \Illuminate\Support\Facades\Cache::put('key', 'value', 60 * 60);

        $this->arrayStore->put('key', 'value', 60 * 60);

        $seconds = 60;
        $this->arrayStore->putMany(['key' => 'value'], $seconds * 60);
    }
}

?>
