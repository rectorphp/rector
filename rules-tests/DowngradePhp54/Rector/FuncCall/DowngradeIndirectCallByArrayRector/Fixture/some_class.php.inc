<?php

namespace Rector\Tests\DowngradePhp54\Rector\FuncCall\DowngradeIndirectCallByArrayRector\Fixture;

final class SomeClass
{
    public static function run($msg) {
        echo "$msg\n";
    }
}

$func = array('SomeClass','run');
$func('hello');

?>
-----
<?php

namespace Rector\Tests\DowngradePhp54\Rector\FuncCall\DowngradeIndirectCallByArrayRector\Fixture;

final class SomeClass
{
    public static function run($msg) {
        echo "$msg\n";
    }
}

$func = array('SomeClass','run');
call_user_func($func, 'hello');

?>
