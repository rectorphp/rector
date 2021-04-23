<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

interface A
{
    public function test(array $input);
}

class B implements A
{
    public function test($input)
    {
    }
}

final class MostChild implements A
{
    public function test(array $input)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

interface A
{
    /**
     * @param mixed[] $input
     */
    public function test($input);
}

class B implements A
{
    public function test($input)
    {
    }
}

final class MostChild implements A
{
    /**
     * @param mixed[] $input
     */
    public function test($input)
    {
    }
}

?>
