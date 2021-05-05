<?php

namespace Rector\Tests\DowngradePhp53\Rector\Dir\DirConstToFileConstRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return __DIR__;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp53\Rector\Dir\DirConstToFileConstRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return dirname(__FILE__);
    }
}

?>
