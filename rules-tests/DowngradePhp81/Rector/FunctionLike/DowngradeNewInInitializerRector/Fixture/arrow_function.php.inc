<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

$func = fn (stdClass $obj = new stdClass) => $obj;

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

$func = function (stdClass $obj = null) {
    $obj ??= new stdClass;
    return $obj;
};

?>
