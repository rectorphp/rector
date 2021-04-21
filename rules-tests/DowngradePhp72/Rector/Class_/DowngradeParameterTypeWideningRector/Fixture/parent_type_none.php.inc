<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

interface ParentWithString
{
    public function test(string $input);
}

class ChildString implements ParentWithString
{
    public function test($input)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

interface ParentWithString
{
    /**
     * @param string $input
     */
    public function test($input);
}

class ChildString implements ParentWithString
{
    public function test($input)
    {
    }
}

?>
