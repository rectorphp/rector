<?php

namespace Rector\Laravel\Tests\Rector\StaticCall\MinutesToSecondsInCacheRector\Fixture;

use Rector\Laravel\Tests\Rector\StaticCall\MinutesToSecondsInCacheRector\Source\ArrayStore;

class SomeClass
{
    /**
     * @var ArrayStore
     */
    private $arrayStore;

    public function __construct(ArrayStore $arrayStore)
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

namespace Rector\Laravel\Tests\Rector\StaticCall\MinutesToSecondsInCacheRector\Fixture;

use Rector\Laravel\Tests\Rector\StaticCall\MinutesToSecondsInCacheRector\Source\ArrayStore;

class SomeClass
{
    /**
     * @var ArrayStore
     */
    private $arrayStore;

    public function __construct(ArrayStore $arrayStore)
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
