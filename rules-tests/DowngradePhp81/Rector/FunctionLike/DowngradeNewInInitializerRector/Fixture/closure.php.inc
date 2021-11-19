<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

$func = function(stdClass $obj = new stdClass, $arg = null) {
    var_dump($arg);
};

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

$func = function(stdClass $obj = null, $arg = null) {
    $obj ??= new stdClass;
    var_dump($arg);
};

?>
