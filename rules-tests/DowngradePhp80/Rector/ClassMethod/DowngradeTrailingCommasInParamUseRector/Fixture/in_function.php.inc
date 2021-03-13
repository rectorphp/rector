<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

function inFunction(string $value1, string $value2,)
{
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

function inFunction(string $value1, string $value2)
{
}

?>
