<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeDirnameLevelsRector\Fixture;

class Fixture
{
    public function dirname($path, $levels)
    {
        return dirname($path, $levels);
    }

    public function simpleDirname($path)
    {
        return dirname($path);
    }

    public function dirnameWithFixedLevels($path)
    {
        dirname($path, 1);
        dirname($path, 2);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeDirnameLevelsRector\Fixture;

class Fixture
{
    public function dirname($path, $levels)
    {
        $dirnameFunc = function ($path, $levels) {
            $dir = null;
            while (--$levels >= 0) {
                $dir = dirname($dir ?: $path);
            }
            return $dir;
        };
        return $dirnameFunc($path, $levels);
    }

    public function simpleDirname($path)
    {
        return dirname($path);
    }

    public function dirnameWithFixedLevels($path)
    {
        dirname($path);
        dirname(dirname($path));
    }
}

?>
