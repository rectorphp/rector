<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

use Symfony\Component\Finder\SplFileInfo;

final class SplFileInfoEdgeCase
{
    public function run(?SplFileInfo $fileInfo)
    {
        if ($fileInfo === null) {
            return 100;
        }

        if (! $fileInfo->getRealPath()) {
            return 0;
        }

        return 1;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

use Symfony\Component\Finder\SplFileInfo;

final class SplFileInfoEdgeCase
{
    public function run(?SplFileInfo $fileInfo)
    {
        if ($fileInfo === null) {
            return 100;
        }

        if ($fileInfo->getRealPath() === '') {
            return 0;
        }

        return 1;
    }
}

?>
