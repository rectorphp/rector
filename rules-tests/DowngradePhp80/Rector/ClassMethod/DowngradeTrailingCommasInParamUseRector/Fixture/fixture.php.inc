<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class Fixture
{
    public function __construct(string $value1, string $value2,)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class Fixture
{
    public function __construct(string $value1, string $value2)
    {
    }
}

?>
