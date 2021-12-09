<?php

namespace Rector\Tests\DowngradePhp80\Rector\ArrayDimFetch\DowngradeDereferenceableOperationRector\Fixture;

function run()
{
    return fn (string $str) => "$str"[0];
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ArrayDimFetch\DowngradeDereferenceableOperationRector\Fixture;

function run()
{
    return fn (string $str) => ("$str")[0];
}

?>
