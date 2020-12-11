<?php

namespace Rector\DowngradePhp80\Tests\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

function inFunction(string $value1, string $value2,)
{
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

function inFunction(string $value1, string $value2)
{
}

?>
