<?php

namespace Rector\DowngradePhp71\Tests\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector;

class SomeClass
{
    public function run()
    {
        echo 'abcdef'[-2];
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector;

class SomeClass
{
    public function run()
    {
        echo 'abcdef'[strlen('abcdef') - 2];
    }
}

?>
