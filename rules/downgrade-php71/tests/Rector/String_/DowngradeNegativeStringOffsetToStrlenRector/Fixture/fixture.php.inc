<?php

namespace Rector\DowngradePhp71\Tests\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector\Fixture;

class Fixture
{
    public function run($var)
    {
        echo 'abcdef'[-2];
        echo strpos('aabbcc', 'b', -3);
        echo strpos($var, 'b', -3);
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector\Fixture;

class Fixture
{
    public function run($var)
    {
        echo 'abcdef'[strlen('abcdef') - 2];
        echo strpos('aabbcc', 'b', strlen('aabbcc') - 3);
        echo strpos($var, 'b', strlen($var) - 3);
    }
}

?>
