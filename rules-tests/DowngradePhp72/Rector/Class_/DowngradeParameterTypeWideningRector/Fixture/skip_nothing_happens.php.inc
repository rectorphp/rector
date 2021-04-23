<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

interface JustToBeSure
{
    public function test(array $input);
}

final class UpdateBoth implements JustToBeSure
{
    public function test(array $input)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

interface JustToBeSure
{
    /**
     * @param mixed[] $input
     */
    public function test($input);
}

final class UpdateBoth implements JustToBeSure
{
    /**
     * @param mixed[] $input
     */
    public function test($input)
    {
    }
}

?>
