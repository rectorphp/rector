<?php

namespace Rector\DowngradePhp80\Tests\Rector\Property\DowngradeUnionTypeToDocBlockRector\Fixture;

class FunctionParam
{
    public function run(int|string $value)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\Property\DowngradeUnionTypeToDocBlockRector\Fixture;

class FunctionParam
{
    /**
     * @param int|string $value
     */
    public function run($value)
    {
    }
}

?>
