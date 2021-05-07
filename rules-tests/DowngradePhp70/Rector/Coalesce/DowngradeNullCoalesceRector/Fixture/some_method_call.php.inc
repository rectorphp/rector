<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector\Fixture;

use SplFileInfo;

final class SomeMethodCall
{
    public function run(SplFileInfo $fileInfo)
    {
        return $fileInfo->getRealPath() ?? 'some';
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector\Fixture;

use SplFileInfo;

final class SomeMethodCall
{
    public function run(SplFileInfo $fileInfo)
    {
        return $fileInfo->getRealPath() !== null ? $fileInfo->getRealPath() : 'some';
    }
}

?>
