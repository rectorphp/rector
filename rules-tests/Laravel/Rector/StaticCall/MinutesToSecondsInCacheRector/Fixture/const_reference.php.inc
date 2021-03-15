<?php

namespace Rector\Tests\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector\Fixture;

use Illuminate\Contracts\Cache\Repository;

final class ConstReference
{
    /**
     * @var int
     */
    private const TIME = 60;

    public function run(Repository $repository)
    {
        $repository->remember('key', 'value', self::TIME);
    }
}

?>
-----
<?php

namespace Rector\Tests\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector\Fixture;

use Illuminate\Contracts\Cache\Repository;

final class ConstReference
{
    /**
     * @var int
     */
    private const TIME = 60 * 60;

    public function run(Repository $repository)
    {
        $repository->remember('key', 'value', self::TIME);
    }
}

?>
