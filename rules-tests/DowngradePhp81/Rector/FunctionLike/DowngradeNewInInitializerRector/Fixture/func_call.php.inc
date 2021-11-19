<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

function someFunc($arg, stdClass $obj = new stdClass)
{
    var_dump($arg);
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

function someFunc($arg, stdClass $obj = null)
{
    $obj ??= new stdClass;
    var_dump($arg);
}

?>
