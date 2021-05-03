<?php

namespace Rector\Tests\DowngradePhp71\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector\Fixture;

final class SomeVariable
{
    public function run(string $var)
    {
        echo $var[-2];
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector\Fixture;

final class SomeVariable
{
    public function run(string $var)
    {
        echo $var[strlen($var) - 2];
    }
}

?>
