<?php

namespace Rector\DowngradePhp74\Tests\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector\Fixture;

class Fixture
{
    public function run($handle, $fp)
    {
        fread($handle, $length) === false;
        false === fread($handle, $length);
        fwrite($fp, '1') === false;
        false === fwrite($fp, '1');
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector\Fixture;

class Fixture
{
    public function run($handle, $fp)
    {
        !fread($handle, $length);
        !fread($handle, $length);
        !fwrite($fp, '1');
        !fwrite($fp, '1');
    }
}

?>
